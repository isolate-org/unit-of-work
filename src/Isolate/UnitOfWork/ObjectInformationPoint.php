<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\ObjectClass\Definition;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ObjectInformationPoint
{
    /**
     * @var ChangeBuilder
     */
    private $changeBuilder;

    /**
     * @var array|Definition[]
     */
    private $classDefinitions;

    /**
     * @param array $classDefinitions
     * @throws InvalidArgumentException
     */
    public function __construct($classDefinitions = [])
    {
        if (!is_array($classDefinitions) && !$classDefinitions instanceof \Traversable) {
            throw new InvalidArgumentException("Class definitions collection must be traversable.");
        }

        foreach ($classDefinitions as $definition) {
            if (!$definition instanceof Definition) {
                throw new InvalidArgumentException(
                    "Each element of class definitions collection must be an instance of \\Isolate\\UnitOfWork\\ClassDefinition."
                );
            }
        }

        $this->changeBuilder = new ChangeBuilder();
        $this->classDefinitions = $classDefinitions;
    }

    /**
     * @param $object
     * @return bool
     * @throws InvalidPropertyPathException
     */
    public function isPersisted($object)
    {
        $this->validateObject($object);
        $propertyAccessor = new PropertyAccessor(false, true);
        $classDefinition = $this->getDefinition($object);
        $idPropertyPath = $classDefinition->getIdDefinition()->getPropertyPath();

        try {
            $identity = $propertyAccessor->getValue($object, $idPropertyPath);
        } catch (NoSuchPropertyException $exception) {
            throw new InvalidPropertyPathException(sprintf(
                "Cant access identifier in \"%s\" using \"%s\" property path.",
                $classDefinition->getClassName(),
                $idPropertyPath
            ));
        }

        return !empty($identity) || $identity === 0;
    }

    /**
     * @param $firstObject
     * @param $secondObject
     * @return bool
     */
    public function isEqual($firstObject, $secondObject)
    {
        foreach ($this->getDefinition($firstObject)->getObservedProperties() as $property) {
            if ($this->changeBuilder->isDifferent($firstObject, $secondObject, $property)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $firstObject
     * @param $secondObject
     * @return ChangeSet
     * @throws InvalidPropertyPathException
     * @throws RuntimeException
     */
    public function getChanges($firstObject, $secondObject)
    {
        if ($this->isEqual($firstObject, $secondObject)) {
            throw new RuntimeException("Objects are equal.");
        }

        $changes = [];
        foreach ($this->getDefinition($firstObject)->getObservedProperties() as $property) {
            if ($this->changeBuilder->isDifferent($firstObject, $secondObject, $property)) {
                $changes[] = $this->changeBuilder->buildChange($firstObject, $secondObject, $property);
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
     * @param $object
     * @return Definition
     * @throws RuntimeException
     */
    public function getDefinition($object)
    {
        foreach ($this->classDefinitions as $definition) {
            if ($definition->fitsFor($object)) {
                return $definition;
            }
        }

        throw new RuntimeException(sprintf("Class \"%s\" does not have definition.", get_class($object)));
    }

    /**
     * @param $object
     * @return bool
     */
    private function hasDefinition($object)
    {
        foreach ($this->classDefinitions as $definition) {
            if ($definition->fitsFor($object)) {
                return true;
            }
        }

        return false;
    }
}
