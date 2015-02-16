<?php

namespace Isolate\UnitOfWork\Entity;

interface Identifier
{
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
