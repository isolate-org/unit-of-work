<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Object\PropertyAccessor;
use Isolate\UnitOfWork\Entity\Value\Change;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;

final class ChangeBuilder
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
     * @param Property $property
     * @param $firstObject
     * @param $secondObject
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isDifferent(Property $property, $firstObject, $secondObject)
    {
        $this->validaObjects($firstObject, $secondObject);

        $firstValue = $this->propertyAccessor->getValue($firstObject, $property->getName());
        $secondValue = $this->propertyAccessor->getValue($secondObject, $property->getName());

        if ($this->areArrays($firstValue, $secondValue)) {
            return !$this->arraysAreEqual($firstValue, $secondValue);
        }

        if ($this->areObjects($firstValue, $secondValue)) {
            return $firstValue == $secondValue;
        }

        return $firstValue !== $secondValue;
    }

    /**
     * @param Property $property
     * @param $firstObject
     * @param $secondObject
     * @return Change
     * @throws RuntimeException
     */
    public function buildChange(Property $property, $firstObject, $secondObject)
    {
        if (!$this->isDifferent($property, $firstObject, $secondObject)) {
            throw new RuntimeException("There are no differences between objects properties.");
        }

        $firstValue = $this->propertyAccessor->getValue($firstObject, $property->getName());
        $secondValue = $this->propertyAccessor->getValue($secondObject, $property->getName());

        return new Change($property, $firstValue, $secondValue);
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

    /**
     * Recursively check if both arrays contains exactly same elements
     *
     * @param $firstArray
     * @param $secondArray
     * @return bool
     */
    private function arraysAreEqual($firstArray, $secondArray)
    {
        if (count($firstArray) != count($secondArray)) {
            return false;
        }

        if (array_keys($firstArray) !== array_keys($secondArray)) {
            return false;
        }

        foreach ($firstArray as $index => $firstValueElement) {
            if ($this->areArrays($firstValueElement, $secondArray[$index])) {
                if (!$this->arraysAreEqual($firstValueElement, $secondArray[$index])) {
                    return false;
                }

                continue;
            }

            if ($this->areObjects($firstValueElement, $secondArray[$index])) {
                return $firstValueElement == $secondArray[$index];
            }

            if ($firstValueElement !== $secondArray[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $firstValue
     * @param $secondValue
     * @return bool
     */
    private function areArrays($firstValue, $secondValue)
    {
        return is_array($firstValue) && is_array($secondValue);
    }

    /**
     * @param $firstValue
     * @param $secondValue
     * @return bool
     */
    private function areObjects($firstValue, $secondValue)
    {
        return is_object($firstValue) && is_object($secondValue);
    }
}
