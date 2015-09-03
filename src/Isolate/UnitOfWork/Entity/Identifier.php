<?php

namespace Isolate\UnitOfWork\Entity;

/**
 * @api
 */
interface Identifier
{
    /**
     * @param $object
     * @return boolean
     * 
     * @api
     */
    public function isEntity($object);

    /**
     * @param mixed $entity
     * @return bool
     * 
     * @api
     */
    public function isPersisted($entity);

    /**
     * @param $entity
     * @return mixed
     * 
     * @api
     */
    public function getIdentity($entity);
}
