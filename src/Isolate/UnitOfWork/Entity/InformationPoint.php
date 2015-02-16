<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition\Repository;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class InformationPoint
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
     * @param $entity
     * @return bool
     * @throws InvalidPropertyPathException
     */
    public function isPersisted($entity)
    {
        $propertyAccessor = new PropertyAccessor(false, true);
        $entityDefinition = $this->getDefinition($entity);
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
        $propertyAccessor = new PropertyAccessor(false, true);
        $entityDefinition = $this->getDefinition($entity);
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

        if (empty($identity) || $identity === 0) {
            throw new RuntimeException(sprintf("Entity \"%s\" was not persisted yet.", get_class($entity)));
        }

        return $identity;
    }

    /**
     * @param $entity
     * @return Definition
     * @throws RuntimeException
     */
    public function getDefinition($entity)
    {
        return $this->definitions->getDefinition($entity);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasDefinition($entity)
    {
        return $this->definitions->hasDefinition($entity);
    }
}
