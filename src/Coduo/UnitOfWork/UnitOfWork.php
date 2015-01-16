<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\EditCommand;
use Coduo\UnitOfWork\Command\NewCommand;
use Coduo\UnitOfWork\Command\RemoveCommand;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\RuntimeException;

class UnitOfWork
{
    /**
     * @var ObjectVerifier
     */
    private $objectVerifier;

    /**
     * @var array
     */
    private $states;

    /**
     * @var array
     */
    private $objects;

    /**
     * @var array
     */
    private $originObjects;

    /**
     * @var ObjectRecovery
     */
    private $objectRecovery;

    /**
     * @param ObjectVerifier $objectVerifier
     */
    public function __construct(ObjectVerifier $objectVerifier)
    {
        $this->objectVerifier = $objectVerifier;
        $this->states = [];
        $this->objects = [];
        $this->originObjects = [];
        $this->objectRecovery = new ObjectRecovery();
    }

    /**
     * @param $object
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function register($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Only object can be register.");
        }

        $hash = spl_object_hash($object);

        $this->objects[$hash] = $object;
        $this->originObjects[$hash] = clone($object);

        $this->states[$hash] = $this->objectVerifier->isPersisted($object)
            ? ObjectStates::PERSISTED_OBJECT
            : ObjectStates::NEW_OBJECT;
    }

    /**
     * @param $object
     * @return bool
     */
    public function isRegistered($object)
    {
        return array_key_exists(spl_object_hash($object), $this->states);
    }

    /**
     * @param $object
     * @return int
     * @throws RuntimeException
     */
    public function getObjectState($object)
    {
        if (!$this->isRegistered($object)) {
            throw new RuntimeException("Object need to be registered first in the Unit of Work.");
        }

        if (!$this->objectVerifier->isEqual($object, $this->originObjects[spl_object_hash($object)])) {
            return ObjectStates::EDITED_OBJECT;
        }

        return $this->states[spl_object_hash($object)];
    }

    /**
     * @param $object
     * @throws RuntimeException
     */
    public function remove($object)
    {
        if (!$this->isRegistered($object)) {
            if (!$this->objectVerifier->isPersisted($object)) {
                throw new RuntimeException("Unit of Work can't remove not persisted objects.");
            }

            $this->register($object);
        }

        $this->states[spl_object_hash($object)] = ObjectStates::REMOVED_OBJECT;
    }

    public function commit()
    {
        $removedObjectHashes = [];

        foreach ($this->objects as $objectHash => $object) {
            $originObject = $this->originObjects[$objectHash];
            $objectClassDefinition = $this->objectVerifier->getDefinition($object);

            $commandResult = null;
            switch($this->getObjectState($object)) {
                case ObjectStates::NEW_OBJECT:
                    $commandResult = $this->handleNewObject($objectClassDefinition, $object);
                    break;
                case ObjectStates::EDITED_OBJECT:
                    $commandResult = $this->handleEditedObject($objectClassDefinition, $object, $originObject);
                    break;
                case ObjectStates::REMOVED_OBJECT:
                    $removedObjectHashes[] = $objectHash;
                    $commandResult = $this->handleRemovedObject($objectClassDefinition, $object);
                    break;
            }

            if ($commandResult === false) {
                $this->rollback();
                return ;
            }
        }

        foreach ($removedObjectHashes as $hash) {
            $this->unregisterObject($hash);
        }

        unset($removedObjectHashes);
    }

    public function rollback()
    {
        foreach ($this->originObjects as $hash => $originObject) {
            $this->objectRecovery->recover($this->objects[$hash], $originObject);
        }
    }

    /**
     * @param $objectClassDefinition
     * @param $object
     */
    private function handleNewObject(ClassDefinition $objectClassDefinition, $object)
    {
        if ($objectClassDefinition->hasNewCommandHandler()) {
            return $objectClassDefinition->getNewCommandHandler()->handle(
                new NewCommand($object)
            );
        }
    }

    /**
     * @param $objectClassDefinition
     * @param $object
     * @param $originObject
     * @throws RuntimeException
     */
    private function handleEditedObject(ClassDefinition $objectClassDefinition, $object, $originObject)
    {
        if ($objectClassDefinition->hasEditCommandHandler()) {
            return $objectClassDefinition->getEditCommandHandler()
                ->handle(new EditCommand($object, $this->objectVerifier->getChanges(
                    $originObject,
                    $object
                )));
        }
    }
    /**
     * @param $objectClassDefinition
     * @param $object
     * @throws RuntimeException
     */
    private function handleRemovedObject(ClassDefinition $objectClassDefinition, $object)
    {
        if ($objectClassDefinition->hasRemoveCommandHandler()) {
            return $objectClassDefinition->getRemoveCommandHandler()
                ->handle(new RemoveCommand($object));
        }
    }

    /**
     * @param $hash
     */
    private function unregisterObject($hash)
    {
        unset($this->states[$hash]);
        unset($this->objects[$hash]);
        unset($this->originObjects[$hash]);
    }
}
