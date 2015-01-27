<?php

namespace Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Object\PropertyAccessor;

class RecoveryPoint
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
     * @param $object
     * @param $sourceObject
     */
    public function recover($object, $sourceObject)
    {
        $this->validaObjects($object, $sourceObject);

        $reflection = new \ReflectionClass($object);
        foreach ($reflection->getProperties() as $property) {

            $this->propertyAccessor->setValue(
                $object,
                $property->getName(),
                $this->propertyAccessor->getValue($sourceObject, $property->getName())
            );
        }
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
