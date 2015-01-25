<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Object\PropertyAccessor;

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
     * @param $firstObject
     * @param $secondObject
     * @param string $propertyName
     * @return bool
     * @throws NotExistingPropertyException
     */
    public function isDifferent($firstObject, $secondObject, $propertyName)
    {
        $this->validaObjects($firstObject, $secondObject);

        $firstValue = $this->propertyAccessor->getValue($firstObject, $propertyName);
        $secondValue = $this->propertyAccessor->getValue($secondObject, $propertyName);

        if ($this->areArrays($firstValue, $secondValue)) {
            return !$this->arraysAreEqual($firstValue, $secondValue);
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

        $firstValue = $this->propertyAccessor->getValue($firstObject, $propertyName);
        $secondValue = $this->propertyAccessor->getValue($secondObject, $propertyName);

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
}
