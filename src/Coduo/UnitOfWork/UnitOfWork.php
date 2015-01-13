<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\RuntimeException;

class UnitOfWork
{
    /**
     * @var ObjectVerifier
     */
    private $objectVerifier;

    private $states;

    private $origins;

    /**
     * @param ObjectVerifier $objectVerifier
     */
    public function __construct(ObjectVerifier $objectVerifier)
    {
        $this->objectVerifier = $objectVerifier;
        $this->states = [];
        $this->origins = [];
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

        $this->origins[$hash] = clone($object);
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

        if (!$this->objectVerifier->isEqual($object, $this->origins[spl_object_hash($object)])) {
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
}
