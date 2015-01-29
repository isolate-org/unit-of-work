<?php

namespace Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

final class Property
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @throws InvalidArgumentException
     */
    public function __construct($name)
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Property name can't be empty.");
        }

        if (!is_string($name)) {
            throw new InvalidArgumentException("Property name must be a valid string.");
        }

        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
