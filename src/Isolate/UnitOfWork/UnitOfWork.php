<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Comparer;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Object\Registry;
use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Event\PostCommit;
use Isolate\UnitOfWork\Event\PreGetState;
use Isolate\UnitOfWork\Event\PreRegister;
use Isolate\UnitOfWork\Event\PreRemove;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Entity\Definition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UnitOfWork
{
    /**
     * @var Registry
     */
    private $registry;

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
     * @param Registry $registry
     * @param InformationPoint $entityInformationPoint
     * @param EventDispatcherInterface $eventDispatcher
     * @param Comparer $entityComparer
     */
    public function __construct(
        Registry $registry,
        InformationPoint $entityInformationPoint,
        Comparer $entityComparer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->registry = $registry;
        $this->entityInformationPoint = $entityInformationPoint;
        $this->eventDispatcher = $eventDispatcher;
        $this->changeBuilder = new ChangeBuilder($entityInformationPoint);
        $this->comparer = $entityComparer;
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

        $this->registry->register($entity);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function isRegistered($entity)
    {
        return $this->registry->isRegistered($entity);
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

        if ($this->registry->isRemoved($entity)) {
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
        }

        $this->registry->remove($entity);
    }

    public function commit()
    {
        $this->eventDispatcher->dispatch(Events::PRE_COMMIT);

        foreach ($this->registry->all() as $entity) {
            $entityClassDefinition = $this->entityInformationPoint->getDefinition($entity);

            $commandResult = null;
            switch($this->getEntityState($entity)) {
                case EntityStates::NEW_ENTITY:
                    $commandResult = $this->handleNewObject($entityClassDefinition, $entity);
                    break;
                case EntityStates::EDITED_ENTITY:
                    $commandResult = $this->handleEditedObject($entityClassDefinition, $entity, $this->registry->getSnapshot($entity));
                    break;
                case EntityStates::REMOVED_ENTITY:
                    $commandResult = $this->handleRemovedObject($entityClassDefinition, $entity);
                    break;
            }

            if ($commandResult === false) {
                $this->rollback();
                $this->eventDispatcher->dispatch(Events::POST_COMMIT, new PostCommit(false));
                return ;
            }
        }

        $this->registry->cleanRemoved();
        $this->registry->makeNewSnapshots();

        $this->eventDispatcher->dispatch(Events::POST_COMMIT, new PostCommit());
    }

    public function rollback()
    {
        $this->registry->reset();
    }

    /**
     * @param $entityClassDefinition
     * @param $entity
     */
    private function handleNewObject(Definition $entityClassDefinition, $entity)
    {
        if ($entityClassDefinition->hasNewCommandHandler()) {
            return $entityClassDefinition->getNewCommandHandler()->handle(
                new NewCommand($entity)
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
            $changeSet = $this->changeBuilder->buildChanges($originEntity, $entity);

            return $entityClassDefinition->getEditCommandHandler()
                ->handle(new EditCommand($entity, $changeSet));
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
                ->handle(new RemoveCommand($entity));
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
            $this->registry->getSnapshot($entity)
        );
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
