<?php

namespace Isolate\UnitOfWork\Entity\Identifier;

use Isolate\UnitOfWork\Entity\Definition\Repository;
use Isolate\UnitOfWork\Entity\Identifier;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Object\PropertyAccessor;

class EntityIdentifier implements Identifier
{
    /**
     * @var Repository
     */
    private $definitions;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @param Repository $definitions
     */
    public function __construct(Repository $definitions)
    {
        $this->definitions = $definitions;
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @param $object
     * @return boolean
     */
    public function isEntity($object)
    {
        return $this->definitions->hasDefinition($object);
    }

    /**
     * @param mixed $entity
     * @return bool
     * @throws RuntimeException
     */
    public function isPersisted($entity)
    {
        $this->validateEntity($entity);
        $entityDefinition = $this->definitions->getDefinition($entity);

        return $entityDefinition->getIdentityStrategy()->isIdentified($entity);

    }

    /**
     * @param $entity
     * @return mixed
     * @throws RuntimeException
     */
    public function getIdentity($entity)
    {
        $this->validateEntity($entity);
        $entityDefinition = $this->definitions->getDefinition($entity);

        return $entityDefinition->getIdentityStrategy()->getIdentity($entity);
    }

    /**
     * @param $entity
     * @throws RuntimeException
     */
    private function validateEntity($entity)
    {
        if (!$this->definitions->hasDefinition($entity)) {
            throw new RuntimeException(sprintf("Class \"%s\" does not have definition.", get_class($entity)));
        }
    }
}
