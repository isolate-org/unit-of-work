<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ObjectRecovery
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);
    }

    public function recover($object, $sourceObject)
    {
        $this->validaObjects($object, $sourceObject);

        $reflection = new \ReflectionClass($object);
        foreach ($reflection->getProperties() as $property) {
            $setNotAccessible = false;
            if (!$property->isPublic()) {
                $setNotAccessible = true;
                $property->setAccessible(true);
            }

            $property->setValue($object, $property->getValue($sourceObject));

            if ($setNotAccessible) {
                $property->setAccessible(false);
            }
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
