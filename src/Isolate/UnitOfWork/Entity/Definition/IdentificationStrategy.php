<?php

namespace Isolate\UnitOfWork\Entity\Definition;

/**
 * @api
 */
interface IdentificationStrategy 
{
    /**
     * @param mixed $entity
     * @return boolean
     * 
     * @api
     */
    public function isIdentified($entity);

    /**
     * @param $entity
     * @return mixed
     * 
     * @api
     */
    public function getIdentity($entity);
}
