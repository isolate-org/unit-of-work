<?php

namespace Isolate\UnitOfWork\Entity\Definition;

interface IdentificationStrategy 
{
    /**
     * @param mixed $entity
     * @return boolean
     */
    public function isIdentified($entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function getIdentity($entity);
}
