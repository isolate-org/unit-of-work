<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

/**
 * @api
 */
final class ClassName
{
    /**
     * @var string
     */
    private $className;

    /**
     * @param string $className
     * @throws InvalidArgumentException
     */
    public function __construct($className)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException("Class name must be a valid string.");
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf("Class \"%s\" does not exists.", $className));
        }

        $this->className = $className;
    }

    public function __toString()
    {
        return $this->className;
    }

    /**
     * @param $entity
     * @return bool
     * 
     * @api
     */
    public function isClassOf($entity)
    {
        return $entity instanceof $this->className;
    }
}
