<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;

final class IdDefinition
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @param string $propertyPath
     * @throws InvalidArgumentException
     */
    public function __construct($propertyPath)
    {
        if (!is_string($propertyPath)) {
            throw new InvalidArgumentException("Property path must be a valid string.");
        }

        if (empty($propertyPath)) {
            throw new InvalidArgumentException("Property path can't be empty.");
        }

        $this->propertyPath = $propertyPath;
    }

    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    public function itFits($propertyPath)
    {
        return $this->propertyPath === $propertyPath;
    }
}
