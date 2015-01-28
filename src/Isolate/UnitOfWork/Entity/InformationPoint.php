<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\ChangeBuilder;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class InformationPoint
{
    /**
     * @var ChangeBuilder
     */
    private $changeBuilder;

    /**
     * @var array|Definition[]
     */
    private $entityDefinitions;

    /**
     * @param array $entityDefinitions
     * @throws InvalidArgumentException
     */
    public function __construct($entityDefinitions = [])
    {
        if (!is_array($entityDefinitions) && !$entityDefinitions instanceof \Traversable) {
            throw new InvalidArgumentException("Class definitions collection must be traversable.");
        }

        foreach ($entityDefinitions as $definition) {
            if (!$definition instanceof Definition) {
                throw new InvalidArgumentException(
                    "Each element of class definitions collection must be an instance of \\Isolate\\UnitOfWork\\ClassDefinition."
                );
            }
        }

        $this->changeBuilder = new ChangeBuilder();
        $this->entityDefinitions = $entityDefinitions;
    }

    /**
     * @param $entity
     * @return bool
     * @throws InvalidPropertyPathException
     */
    public function isPersisted($entity)
    {
        $this->validateObject($entity);
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
     * @param $firstEntity
     * @param $secondEntity
     * @return bool
     */
    public function areEqual($firstEntity, $secondEntity)
    {
        foreach ($this->getDefinition($firstEntity)->getObservedProperties() as $property) {
            if ($this->changeBuilder->isDifferent($property, $firstEntity, $secondEntity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $firstEntity
     * @param $secondEntity
     * @return \Isolate\UnitOfWork\Value\\Isolate\UnitOfWork\Entity\Value\ChangeSet
     * @throws InvalidPropertyPathException
     * @throws RuntimeException
     */
    public function getChanges($firstEntity, $secondEntity)
    {
        if ($this->areEqual($firstEntity, $secondEntity)) {
            throw new RuntimeException("Objects are equal.");
        }

        $changes = [];
        foreach ($this->getDefinition($firstEntity)->getObservedProperties() as $property) {
            if ($this->changeBuilder->isDifferent($property, $firstEntity, $secondEntity)) {
                $changes[] = $this->changeBuilder->buildChange($property, $firstEntity, $secondEntity);
            }
        }

        return new ChangeSet($changes);
    }

    /**
     * @param $object
     * @throws RuntimeException
     */
    private function validateObject($object)
    {
        if (!$this->hasDefinition($object)) {
            throw new RuntimeException(sprintf("Class \"%s\" does not have definition.", get_class($object)));
        }
    }


    /**
     * @param $entity
     * @return Definition
     * @throws RuntimeException
     */
    public function getDefinition($entity)
    {
        foreach ($this->entityDefinitions as $definition) {
            if ($definition->fitsFor($entity)) {
                return $definition;
            }
        }

        throw new RuntimeException(sprintf("Class \"%s\" does not have definition.", get_class($entity)));
    }

    /**
     * @param $entity
     * @return bool
     */
    public function hasDefinition($entity)
    {
        foreach ($this->entityDefinitions as $definition) {
            if ($definition->fitsFor($entity)) {
                return true;
            }
        }

        return false;
    }
}
