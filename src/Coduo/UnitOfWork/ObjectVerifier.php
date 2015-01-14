<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\InvalidPropertyPathException;
use Coduo\UnitOfWork\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ObjectVerifier
{
    /**
     * @var array|ClassDefinition[]
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
            if (!$definition instanceof ClassDefinition) {
                throw new InvalidArgumentException(
                    "Each element of class definitions collection must be an instance of \\Coduo\\UnitOfWork\\ClassDefinition."
                );
            }
        }
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

        return !empty($identity);
    }

    /**
     * @param $firstObject
     * @param $secondObject
     * @return bool
     */
    public function isEqual($firstObject, $secondObject)
    {
        return $firstObject == $secondObject;
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
     * @return ClassDefinition
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
