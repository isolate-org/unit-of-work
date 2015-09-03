<?php

namespace Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Exception\RuntimeException;

/**
 * Interface Repository
 * @package Isolate\UnitOfWork\Entity\Definition
 * 
 * @api
 */
interface Repository
{
    /**
     * @param $entity
     * @return boolean
     * 
     * @api
     */
    public function hasDefinition($entity);

    /**
     * @param $entity
     * @return Definition
     * @throws RuntimeException
     * 
     * @api
     */
    public function getDefinition($entity);
}
