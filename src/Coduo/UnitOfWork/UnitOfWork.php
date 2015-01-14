<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\NewCommand;
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
     * @param ObjectVerifier $objectVerifier
     */
    public function __construct(ObjectVerifier $objectVerifier)
    {
        $this->objectVerifier = $objectVerifier;
        $this->states = [];
        $this->objects = [];
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

        if (!$this->objectVerifier->isEqual($object, $this->objects[spl_object_hash($object)])) {
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
        foreach ($this->states as $objectHash => $objectState) {
            $object = $this->objects[$objectHash];
            $objectClassDefinition = $this->objectVerifier->getDefinition($object);

            switch($objectState) {
                case ObjectStates::NEW_OBJECT:
                    $this->handleNewObject($objectClassDefinition, $object);
                    break;
            }
        }
    }

    /**
     * @param $objectClassDefinition
     * @param $object
     */
    private function handleNewObject(ClassDefinition $objectClassDefinition, $object)
    {
        if ($objectClassDefinition->hasNewCommandHandler()) {
            $objectClassDefinition->getNewCommandHandler()->handle(
                new NewCommand($object)
            );
        }
    }
}
