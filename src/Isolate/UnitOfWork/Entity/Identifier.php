<?php

namespace Isolate\UnitOfWork\Entity;

interface Identifier
{
    /**
     * @param $object
     * @return boolean
     */
    public function isEntity($object);

    /**
     * @param mixed $entity
     * @return bool
     */
    public function isPersisted($entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function getIdentity($entity);
}
