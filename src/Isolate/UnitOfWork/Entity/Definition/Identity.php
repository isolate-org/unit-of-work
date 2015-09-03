<?php

namespace Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

/**
 * @api
 */
final class Identity
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @param string $propertyName
     * @throws InvalidArgumentException
     */
    public function __construct($propertyName)
    {
        if (!is_string($propertyName)) {
            throw new InvalidArgumentException("Property name must be a valid string.");
        }

        if (empty($propertyName)) {
            throw new InvalidArgumentException("Property name can't be empty.");
        }

        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     * 
     * @api
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyPath
     * @return bool
     * 
     * @api
     */
    public function isEqual($propertyPath)
    {
        return $this->propertyName === $propertyPath;
    }
}
