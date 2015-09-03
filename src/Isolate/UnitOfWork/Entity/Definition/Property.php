<?php

namespace Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

/**
 * @api
 */
final class Property
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var null|Association
     */
    private $association;

    /**
     * @param string $name
     * @param null|Association $association
     * @throws InvalidArgumentException
     */
    public function __construct($name, Association $association = null)
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Property name can't be empty.");
        }

        if (!is_string($name)) {
            throw new InvalidArgumentException("Property name must be a valid string.");
        }

        $this->name = $name;
        $this->association = $association;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     * 
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     * 
     * @api
     */
    public function isAssociated()
    {
        return !is_null($this->association);
    }

    /**
     * @return null|Association
     * 
     * @api
     */
    public function getAssociation()
    {
        return $this->association;
    }
}
