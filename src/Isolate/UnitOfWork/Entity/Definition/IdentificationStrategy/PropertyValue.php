<?php

namespace Isolate\UnitOfWork\Entity\Definition\IdentificationStrategy;

use Isolate\UnitOfWork\Entity\Definition\IdentificationStrategy;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Object\PropertyAccessor;

class PropertyValue implements IdentificationStrategy
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @param mixed $entity
     * @return boolean
     */
    public function isIdentified($entity)
    {
        $identity = $this->propertyAccessor->getValue($entity, $this->identity->getPropertyName());

        return !empty($identity) || $identity === 0;
    }

    /**
     * @param $entity
     * @return mixed
     * @throws RuntimeException
     */
    public function getIdentity($entity)
    {
        $identity = $this->propertyAccessor->getValue($entity, $this->identity->getPropertyName());

        if (empty($identity) && $identity !== 0) {
            throw new RuntimeException("Can't get identity from not identified entity.");
        }

        return $identity;
    }
}
