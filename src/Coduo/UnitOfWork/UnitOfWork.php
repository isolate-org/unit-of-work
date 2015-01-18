<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\EditCommand;
use Coduo\UnitOfWork\Command\NewCommand;
use Coduo\UnitOfWork\Command\RemoveCommand;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\RuntimeException;
use Coduo\UnitOfWork\ObjectClass\Definition;

class UnitOfWork
{
    /**
     * @var ObjectInformationPoint
     */
    private $objectInformationPoint;

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
     * @var int
     */
    private $totalNewObjects;

    /**
     * @var int
     */
    private $totalEditedObjects;

    /**
     * @var int
     */
    private $totalRemovedObjects;

    /**
     * @param ObjectInformationPoint $objectInformationPoint
     */
    public function __construct(ObjectInformationPoint $objectInformationPoint)
    {
        $this->objectInformationPoint = $objectInformationPoint;
        $this->states = [];
        $this->objects = [];
        $this->originObjects = [];
        $this->objectRecovery = new ObjectRecovery();
        $this->totalNewObjects = 0;
        $this->totalEditedObjects = 0;
        $this->totalRemovedObjects = 0;
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

        $this->states[$hash] = $this->objectInformationPoint->isPersisted($object)
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

        if ($this->states[spl_object_hash($object)] === ObjectStates::NEW_OBJECT) {
            return ObjectStates::NEW_OBJECT;
        }

        if (!$this->objectInformationPoint->isEqual($object, $this->originObjects[spl_object_hash($object)])) {
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
            if (!$this->objectInformationPoint->isPersisted($object)) {
                throw new RuntimeException("Unit of Work can't remove not persisted objects.");
            }

            $this->register($object);
        }

        $this->states[spl_object_hash($object)] = ObjectStates::REMOVED_OBJECT;
    }

    public function commit()
    {
        $removedObjectHashes = [];
        $this->countObjects();

        foreach ($this->objects as $objectHash => $object) {
            $originObject = $this->originObjects[$objectHash];
            $objectClassDefinition = $this->objectInformationPoint->getDefinition($object);

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

        $this->unregisterObjects($removedObjectHashes);
        $this->updateObjectsAndStates();

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
    private function handleNewObject(Definition $objectClassDefinition, $object)
    {
        if ($objectClassDefinition->hasNewCommandHandler()) {
            return $objectClassDefinition->getNewCommandHandler()->handle(
                new NewCommand($object, $this->totalNewObjects)
            );
        }
    }

    /**
     * @param $objectClassDefinition
     * @param $object
     * @param $originObject
     * @throws RuntimeException
     */
    private function handleEditedObject(Definition $objectClassDefinition, $object, $originObject)
    {
        if ($objectClassDefinition->hasEditCommandHandler()) {
            return $objectClassDefinition->getEditCommandHandler()
                ->handle(new EditCommand(
                    $object,
                    $this->objectInformationPoint->getChanges(
                        $originObject,
                        $object
                    ),
                    $this->totalEditedObjects
                ));
        }
    }

    /**
     * @param $objectClassDefinition
     * @param $object
     * @throws RuntimeException
     */
    private function handleRemovedObject(Definition $objectClassDefinition, $object)
    {
        if ($objectClassDefinition->hasRemoveCommandHandler()) {
            return $objectClassDefinition->getRemoveCommandHandler()
                ->handle(new RemoveCommand($object, $this->totalRemovedObjects));
        }
    }

    /**
     * @param $removedObjectHashes
     */
    private function unregisterObjects($removedObjectHashes)
    {
        foreach ($removedObjectHashes as $hash) {
            unset($this->states[$hash]);
            unset($this->objects[$hash]);
            unset($this->originObjects[$hash]);
        }
    }

    private function updateObjectsAndStates()
    {
        foreach ($this->objects as $objectHash => $object) {
            $this->originObjects[$objectHash] = $object;
            $this->states[$objectHash] = ObjectStates::PERSISTED_OBJECT;
        }
    }

    private function countObjects()
    {
        $this->totalNewObjects = 0;
        $this->totalEditedObjects = 0;
        $this->totalRemovedObjects = 0;

        foreach ($this->objects as $objectHash => $object) {
            switch($this->getObjectState($object)) {
                case ObjectStates::NEW_OBJECT:
                    $this->totalNewObjects++;
                    break;
                case ObjectStates::EDITED_OBJECT:
                    $this->totalEditedObjects++;
                    break;
                case ObjectStates::REMOVED_OBJECT:
                    $this->totalRemovedObjects++;
                    break;
            }
        }
    }
}
