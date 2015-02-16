<?php

namespace Isolate\UnitOfWork\Entity\Identifier\Symfony;

use Isolate\UnitOfWork\Entity\Definition\Repository;
use Isolate\UnitOfWork\Entity\Identifier;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class PropertyAccessorIdentifier implements Identifier
{
    /**
     * @var Repository
     */
    private $definitions;

    /**
     * @param Repository $definitions
     */
    public function __construct(Repository $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @param mixed $entity
     * @return bool
     * @throws InvalidPropertyPathException
     * @throws RuntimeException
     */
    public function isPersisted($entity)
    {
        $this->validateEntity($entity);
        $propertyAccessor = new PropertyAccessor(false, true);
        $entityDefinition = $this->definitions->getDefinition($entity);
        $idPropertyPath = $entityDefinition->getIdDefinition()->getPropertyPath();

        try {
            $identity = $propertyAccessor->getValue($entity, $idPropertyPath);
        } catch (NoSuchPropertyException $exception) {
            throw new InvalidPropertyPathException(sprintf(
                "Cant access identifier in \"%s\" using \"%s\" property path.",
                $entityDefinition->getClassName(),
                $idPropertyPath
            ));
        }

        return !empty($identity) || $identity === 0;
    }

    /**
     * @param $entity
     * @return bool
     * @throws InvalidPropertyPathException
     * @throws RuntimeException
     */
    public function getIdentity($entity)
    {
        $this->validateEntity($entity);
        $propertyAccessor = new PropertyAccessor(false, true);
        $entityDefinition = $this->definitions->getDefinition($entity);
        $idPropertyPath = $entityDefinition->getIdDefinition()->getPropertyPath();

        try {
            $identity = $propertyAccessor->getValue($entity, $idPropertyPath);
        } catch (NoSuchPropertyException $exception) {
            throw new InvalidPropertyPathException(sprintf(
                "Cant access identifier in \"%s\" using \"%s\" property path.",
                $entityDefinition->getClassName(),
                $idPropertyPath
            ));
        }

        if (empty($identity) && $identity !== 0) {
            throw new RuntimeException(sprintf("Entity \"%s\" was not persisted yet.", get_class($entity)));
        }

        return $identity;
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
