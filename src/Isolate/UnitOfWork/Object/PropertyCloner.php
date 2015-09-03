<?php

namespace Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;

/**
 * @api
 */
class PropertyCloner
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @param $target
     * @param $source
     * 
     * @api
     */
    public function cloneProperties($target, $source)
    {
        $this->validaObjects($target, $source);

        $reflection = new \ReflectionClass($target);
        foreach ($reflection->getProperties() as $property) {

            $this->propertyAccessor->setValue(
                $target,
                $property->getName(),
                $this->propertyAccessor->getValue($source, $property->getName())
            );
        }
    }

    /**
     * @param $target
     * @param $source
     * @param $propertyName
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     * 
     * @api
     */
    public function cloneProperty($target, $source, $propertyName)
    {
        $this->validaObjects($target, $source);

        $reflection = new \ReflectionClass($target);
        if (!$reflection->hasProperty($propertyName)) {
            throw new NotExistingPropertyException();
        }

        $this->propertyAccessor->setValue(
            $target,
            $propertyName,
            $this->propertyAccessor->getValue($source, $propertyName)
        );
    }

    /**
     * @param $firstObject
     * @param $secondObject
     * @throws InvalidArgumentException
     */
    private function validaObjects($firstObject, $secondObject)
    {
        if (!is_object($firstObject) || !is_object($secondObject)) {
            throw new InvalidArgumentException("Compared values need to be a valid objects.");
        }

        if (get_class($firstObject) !== get_class($secondObject)) {
            throw new InvalidArgumentException("Compared values need to be an instances of the same class.");
        }
    }
}
