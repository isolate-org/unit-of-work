<?php

namespace Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Exception\RuntimeException;

interface Repository
{
    /**
     * @param $entity
     * @return boolean
     */
    public function hasDefinition($entity);

    /**
     * @param $entity
     * @return Definition
     * @throws RuntimeException
     */
    public function getDefinition($entity);
}
