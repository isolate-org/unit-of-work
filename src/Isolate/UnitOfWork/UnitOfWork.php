<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Comparer;
use Isolate\UnitOfWork\Entity\Identifier;
use Isolate\UnitOfWork\Object\Registry;
use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Entity\Definition;

class UnitOfWork
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Identifier
     */
    private $identifier;

    /**
     * @var ChangeBuilder
     */
    private $changeBuilder;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @param Registry $registry
     * @param Identifier $identifier
     * @param ChangeBuilder $changeBuilder
     * @param Comparer $entityComparer
     * @param CommandBus $commandBus
     */
    public function __construct(
        Registry $registry,
        Identifier $identifier,
        ChangeBuilder $changeBuilder,
        Comparer $entityComparer,
        CommandBus $commandBus
    ) {
        $this->registry = $registry;
        $this->identifier = $identifier;
        $this->changeBuilder = $changeBuilder;
        $this->comparer = $entityComparer;
        $this->commandBus = $commandBus;
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

        if (!$this->identifier->isEntity($entity)) {
            throw new InvalidArgumentException("Only entities can be registered in Unit of Work.");
        }

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
     * @throws Exception\InvalidPropertyPathException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function remove($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        if (!$this->isRegistered($entity)) {
            if (!$this->identifier->isPersisted($entity)) {
                throw new RuntimeException("Unit of Work can't remove not persisted entities.");
            }
        }

        $this->registry->remove($entity);
    }

    public function commit()
    {
        foreach ($this->registry->all() as $entity) {

            $commandResult = null;
            switch($this->getEntityState($entity)) {
                case EntityStates::NEW_ENTITY:
                    $commandResult = $this->commandBus->dispatch(new NewCommand($entity));
                    break;
                case EntityStates::EDITED_ENTITY:
                    $changeSet = $this->changeBuilder->buildChanges($this->registry->getSnapshot($entity), $entity);
                    $commandResult = $this->commandBus->dispatch(new EditCommand($entity, $changeSet));
                    break;
                case EntityStates::REMOVED_ENTITY:
                    $commandResult = $this->commandBus->dispatch(new RemoveCommand($entity));
                    break;
            }

            if ($commandResult === false) {
                $this->rollback();
                return ;
            }
        }

        $this->registry->cleanRemoved();
        $this->registry->makeNewSnapshots();
    }

    public function rollback()
    {
        $this->registry->reset();
    }

    /**
     * @param $entity
     * @return int
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function getEntityState($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        if (!$this->isRegistered($entity)) {
            throw new RuntimeException("Object need to be registered first in the Unit of Work.");
        }

        if ($this->registry->isRemoved($entity)) {
            return EntityStates::REMOVED_ENTITY;
        }

        if (!$this->identifier->isPersisted($entity)) {
            return EntityStates::NEW_ENTITY;
        }

        if ($this->isChanged($entity)) {
            return EntityStates::EDITED_ENTITY;
        }

        return EntityStates::PERSISTED_ENTITY;
    }

    /**
     * @param $entity
     * @return bool
     * @throws RuntimeException
     */
    private function isChanged($entity)
    {
        return !$this->comparer->areEqual(
            $entity,
            $this->registry->getSnapshot($entity)
        );
    }
}
