<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Event\PostCommit;
use Isolate\UnitOfWork\Event\PreGetState;
use Isolate\UnitOfWork\Event\PreRegister;
use Isolate\UnitOfWork\Event\PreRemove;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\ObjectClass\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;

class UnitOfWork
{
    /**
     * @var ObjectInformationPoint
     */
    private $objectInformationPoint;

    /**
     * @var array
     */
    private $removedObjects;

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
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param ObjectInformationPoint $objectInformationPoint
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(ObjectInformationPoint $objectInformationPoint, EventDispatcher $eventDispatcher)
    {
        $this->objectInformationPoint = $objectInformationPoint;
        $this->removedObjects = [];
        $this->objects = [];
        $this->originObjects = [];
        $this->objectRecovery = new ObjectRecovery();
        $this->totalNewObjects = 0;
        $this->totalEditedObjects = 0;
        $this->totalRemovedObjects = 0;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param $object
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function register($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        $event = new PreRegister($object);
        $this->eventDispatcher->dispatch(Events::PRE_REGISTER_OBJECT, $event);
        $object = $event->getObject();

        $hash = spl_object_hash($object);

        $this->objects[$hash] = $object;
        $this->originObjects[$hash] = clone($object);

//        $this->states[$hash] = $this->objectInformationPoint->isPersisted($object)
//            ? ObjectStates::PERSISTED_OBJECT
//            : ObjectStates::NEW_OBJECT;
    }

    /**
     * @param $object
     * @return bool
     */
    public function isRegistered($object)
    {
        return array_key_exists(spl_object_hash($object), $this->objects);
    }

    /**
     * @param $object
     * @return int
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function getObjectState($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        $event = new PreGetState($object);
        $this->eventDispatcher->dispatch(Events::PRE_GET_OBJECT_STATE, $event);
        $object = $event->getObject();

        if (!$this->isRegistered($object)) {
            throw new RuntimeException("Object need to be registered first in the Unit of Work.");
        }

        if (array_key_exists(spl_object_hash($object), $this->removedObjects)) {
            return ObjectStates::REMOVED_OBJECT;
        }

        if (!$this->objectInformationPoint->isPersisted($object)) {
            return ObjectStates::NEW_OBJECT;
        }

        if (!$this->objectInformationPoint->isEqual($object, $this->originObjects[spl_object_hash($object)])) {
            return ObjectStates::EDITED_OBJECT;
        }

        return ObjectStates::PERSISTED_OBJECT;
    }

    /**
     * @param $object
     * @throws Exception\InvalidPropertyPathException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function remove($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Only objects can be registered in Unit of Work.");
        }

        $event = new PreRemove($object);
        $this->eventDispatcher->dispatch(Events::PRE_REMOVE_OBJECT, $event);
        $object = $event->getObject();

        if (!$this->isRegistered($object)) {
            if (!$this->objectInformationPoint->isPersisted($object)) {
                throw new RuntimeException("Unit of Work can't remove not persisted objects.");
            }

            $this->register($object);
        }

        $this->removedObjects[spl_object_hash($object)] = $object;
    }

    public function commit()
    {
        $removedObjectHashes = [];
        $this->countObjects();

        $this->eventDispatcher->dispatch(Events::PRE_COMMIT);

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
                $this->eventDispatcher->dispatch(Events::POST_COMMIT, new PostCommit(false));
                return ;
            }
        }

        $this->unregisterObjects($removedObjectHashes);
        $this->updateObjectsAndStates();

        $this->eventDispatcher->dispatch(Events::POST_COMMIT, new PostCommit());
        unset($removedObjectHashes);
    }

    public function rollback()
    {
        foreach ($this->originObjects as $hash => $originObject) {
            $this->objectRecovery->recover($this->objects[$hash], $originObject);
        }

        $this->removedObjects = [];
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
            unset($this->removedObjects[$hash]);
            unset($this->objects[$hash]);
            unset($this->originObjects[$hash]);
        }
    }

    private function updateObjectsAndStates()
    {
        foreach ($this->objects as $objectHash => $object) {
            $this->originObjects[$objectHash] = $object;
            $this->removedObjects[$objectHash] = ObjectStates::PERSISTED_OBJECT;
        }
    }

    private function countObjects()
    {
        $this->totalNewObjects = 0;
        $this->totalEditedObjects = 0;
        $this->totalRemovedObjects = 0;

        foreach ($this->objects as $object) {
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
