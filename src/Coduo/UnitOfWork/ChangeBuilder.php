<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\NotExistingPropertyException;
use Coduo\UnitOfWork\Exception\RuntimeException;

final class ChangeBuilder
{
    /**
     * @param $firstObject
     * @param $secondObject
     * @param string $propertyName
     * @return bool
     * @throws NotExistingPropertyException
     */
    public function isDifferent($firstObject, $secondObject, $propertyName)
    {
        $this->validaObjects($firstObject, $secondObject);

        $reflection = new \ReflectionClass($firstObject);
        if (!$reflection->hasProperty($propertyName)) {
            throw new NotExistingPropertyException(sprintf(
                "Property \"%s\" does not exists in \"%s\" class.",
                $propertyName,
                get_class($firstObject)
            ));
        }

        $property = $reflection->getProperty($propertyName);

        $setNotAccessible = false;
        if (!$property->isPublic()) {
            $setNotAccessible = true;
            $property->setAccessible(true);
        }

        $firstValue = $property->getValue($firstObject);
        $secondValue = $property->getValue($secondObject);

        if ($setNotAccessible) {
            $property->setAccessible(false);
        }

        return $firstValue !== $secondValue;
    }

    /**
     * @param $firstObject
     * @param $secondObject
     * @param $propertyName
     * @return Change
     * @throws RuntimeException
     */
    public function buildChange($firstObject, $secondObject, $propertyName)
    {
        if (!$this->isDifferent($firstObject, $secondObject, $propertyName)) {
            throw new RuntimeException("There are no differences between objects properties.");
        }

        $reflection = new \ReflectionClass($firstObject);
        $property = $reflection->getProperty($propertyName);

        $setNotAccessible = false;
        if (!$property->isPublic()) {
            $setNotAccessible = true;
            $property->setAccessible(true);
        }

        $firstValue = $property->getValue($firstObject);
        $secondValue = $property->getValue($secondObject);


        if ($setNotAccessible) {
            $property->setAccessible(false);
        }

        return new Change($firstValue, $secondValue, $propertyName);
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
