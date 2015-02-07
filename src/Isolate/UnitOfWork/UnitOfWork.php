<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Comparer;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Object\Cloner\Adapter\DeepCopy\Cloner;
use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Event\PostCommit;
use Isolate\UnitOfWork\Event\PreGetState;
use Isolate\UnitOfWork\Event\PreRegister;
use Isolate\UnitOfWork\Event\PreRemove;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Object\RecoveryPoint;
use Isolate\UnitOfWork\Entity\Definition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UnitOfWork
{
    /**
     * @var ChangeBuilder
     */
    private $changeBuilder;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var InformationPoint
     */
    private $entityInformationPoint;

    /**
     * @var Cloner
     */
    private $cloner;

    /**
     * @var RecoveryPoint
     */
    private $objectRecoveryPoint;

    /**
     * @var array
     */
    private $removedEntities;

    /**
     * @var array
     */
    private $entities;

    /**
     * @var array
     */
    private $originEntities;

    /**
     * @var int
     */
    private $totalNewEntities;

    /**
     * @var int
     */
    private $totalEditedEntities;

    /**
     * @var int
     */
    private $totalRemovedEntities;

    /**
     * @param InformationPoint $entityInformationPoint
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(InformationPoint $entityInformationPoint, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityInformationPoint = $entityInformationPoint;
        $this->eventDispatcher = $eventDispatcher;
        $this->changeBuilder = new ChangeBuilder($entityInformationPoint);
        $this->comparer = new Comparer();
        $this->objectRecoveryPoint = new RecoveryPoint();
        $this->cloner = new Cloner();
        $this->removedEntities = [];
        $this->entities = [];
        $this->originEntities = [];
        $this->totalNewEntities = 0;
        $this->totalEditedEntities = 0;
        $this->totalRemovedEntities = 0;
    }

    /**
     * @param $entity
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function register($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        if (!$this->entityInformationPoint->hasDefinition($entity)) {
            throw new InvalidArgumentException("Only entities can be registered in Unit of Work.");
        }

        $event = new PreRegister($entity);
        $this->eventDispatcher->dispatch(Events::PRE_REGISTER_ENTITY, $event);
        $entity = $event->getEntity();

        $hash = spl_object_hash($entity);

        $this->entities[$hash] = $entity;
        $this->originEntities[$hash] = $this->cloner->cloneObject($entity);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function isRegistered($entity)
    {
        return array_key_exists(spl_object_hash($entity), $this->entities);
    }

    /**
     * @param $entity
     * @return int
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getEntityState($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        $event = new PreGetState($entity);
        $this->eventDispatcher->dispatch(Events::PRE_GET_ENTITY_STATE, $event);
        $entity = $event->getEntity();

        if (!$this->isRegistered($entity)) {
            throw new RuntimeException("Object need to be registered first in the Unit of Work.");
        }

        if ($this->wasRemoved($entity)) {
            return EntityStates::REMOVED_ENTITY;
        }

        if (!$this->isPersisted($entity)) {
            return EntityStates::NEW_ENTITY;
        }

        if ($this->isChanged($entity)) {
            return EntityStates::EDITED_ENTITY;
        }

        return EntityStates::PERSISTED_ENTITY;
    }

    /**
     * @param $entity
     * @throws Exception\InvalidPropertyPathException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function remove($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        $event = new PreRemove($entity);
        $this->eventDispatcher->dispatch(Events::PRE_REMOVE_ENTITY, $event);
        $entity = $event->getEntity();

        if (!$this->isRegistered($entity)) {
            if (!$this->entityInformationPoint->isPersisted($entity)) {
                throw new RuntimeException("Unit of Work can't remove not persisted entities.");
            }

            $this->register($entity);
        }

        $this->removedEntities[spl_object_hash($entity)] = $entity;
    }

    public function commit()
    {
        $removedEntitiesHashes = [];
        $this->countEntities();

        $this->eventDispatcher->dispatch(Events::PRE_COMMIT);

        foreach ($this->entities as $entityHash => $entity) {
            $originEntity = $this->originEntities[$entityHash];
            $entityClassDefinition = $this->entityInformationPoint->getDefinition($entity);

            $commandResult = null;
            switch($this->getEntityState($entity)) {
                case EntityStates::NEW_ENTITY:
                    $commandResult = $this->handleNewObject($entityClassDefinition, $entity);
                    break;
                case EntityStates::EDITED_ENTITY:
                    $commandResult = $this->handleEditedObject($entityClassDefinition, $entity, $originEntity);
                    break;
                case EntityStates::REMOVED_ENTITY:
                    $removedEntitiesHashes[] = $entityHash;
                    $commandResult = $this->handleRemovedObject($entityClassDefinition, $entity);
                    break;
            }

            if ($commandResult === false) {
                $this->rollback();
                $this->eventDispatcher->dispatch(Events::POST_COMMIT, new PostCommit(false));
                return ;
            }
        }

        $this->unregisterEntities($removedEntitiesHashes);
        $this->updateEntitiesStates();

        $this->eventDispatcher->dispatch(Events::POST_COMMIT, new PostCommit());
        unset($removedEntitiesHashes);
    }

    public function rollback()
    {
        foreach ($this->originEntities as $hash => $originEntity) {
            $this->objectRecoveryPoint->recover($this->entities[$hash], $originEntity);
        }

        $this->removedEntities = [];
    }

    /**
     * @param $entityClassDefinition
     * @param $entity
     */
    private function handleNewObject(Definition $entityClassDefinition, $entity)
    {
        if ($entityClassDefinition->hasNewCommandHandler()) {
            return $entityClassDefinition->getNewCommandHandler()->handle(
                new NewCommand($entity, $this->totalNewEntities)
            );
        }
    }

    /**
     * @param $entityClassDefinition
     * @param $entity
     * @param $originEntity
     * @throws RuntimeException
     */
    private function handleEditedObject(Definition $entityClassDefinition, $entity, $originEntity)
    {
        if ($entityClassDefinition->hasEditCommandHandler()) {
            $changeSet = $this->changeBuilder->buildChanges(
                $originEntity,
                $entity
            );

            return $entityClassDefinition->getEditCommandHandler()
                ->handle(new EditCommand(
                    $entity,
                    $changeSet,
                    $this->totalEditedEntities
                ));
        }
    }

    /**
     * @param $entityClassDefinition
     * @param $entity
     * @throws RuntimeException
     */
    private function handleRemovedObject(Definition $entityClassDefinition, $entity)
    {
        if ($entityClassDefinition->hasRemoveCommandHandler()) {
            return $entityClassDefinition->getRemoveCommandHandler()
                ->handle(new RemoveCommand($entity, $this->totalRemovedEntities));
        }
    }

    /**
     * @param $removedEntitiesHashes
     */
    private function unregisterEntities($removedEntitiesHashes)
    {
        foreach ($removedEntitiesHashes as $hash) {
            unset($this->removedEntities[$hash]);
            unset($this->entities[$hash]);
            unset($this->originEntities[$hash]);
        }
    }

    private function updateEntitiesStates()
    {
        $this->removedEntities = [];
        foreach ($this->entities as $entityHash => $entity) {
            $this->originEntities[$entityHash] = $this->cloner->cloneObject($entity);
        }
    }

    private function countEntities()
    {
        $this->totalNewEntities = 0;
        $this->totalEditedEntities = 0;
        $this->totalRemovedEntities = 0;

        foreach ($this->entities as $entity) {
            switch($this->getEntityState($entity)) {
                case EntityStates::NEW_ENTITY:
                    $this->totalNewEntities++;
                    break;
                case EntityStates::EDITED_ENTITY:
                    $this->totalEditedEntities++;
                    break;
                case EntityStates::REMOVED_ENTITY:
                    $this->totalRemovedEntities++;
                    break;
            }
        }
    }

    /**
     * @param $entity
     * @return bool
     * @throws RuntimeException
     */
    private function isChanged($entity)
    {
        return !$this->comparer->areEqual(
            $this->entityInformationPoint->getDefinition($entity),
            $entity,
            $this->originEntities[spl_object_hash($entity)]
        );
    }

    /**
     * @param $entity
     * @return bool
     */
    private function wasRemoved($entity)
    {
        return array_key_exists(spl_object_hash($entity), $this->removedEntities);
    }

    /**
     * @param $entity
     * @return bool
     * @throws Exception\InvalidPropertyPathException
     */
    private function isPersisted($entity)
    {
        return $this->entityInformationPoint->isPersisted($entity);
    }
}
