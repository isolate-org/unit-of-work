<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\InvalidPropertyPathException;
use Coduo\UnitOfWork\Exception\RuntimeException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class ChangeBuilder
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor(false, true);
    }

    public function isDifferent($firstObject, $secondObject, $propertyPath)
    {
        $this->validaObjects($firstObject, $secondObject);

        try {
            $firstValue = $this->propertyAccessor->getValue($firstObject, $propertyPath);
            $secondValue = $this->propertyAccessor->getValue($secondObject, $propertyPath);
        } catch (NoSuchPropertyException $exception) {
            throw new InvalidPropertyPathException(sprintf(
                "Property path \"%s\" does not exists in \"%s\" class.",
                $propertyPath,
                get_class($firstObject)
            ));
        }

        return $firstValue !== $secondValue;
    }

    /**
     * @param $firstObject
     * @param $secondObject
     * @param $propertyPath
     * @return Change
     * @throws InvalidPropertyPathException
     * @throws RuntimeException
     */
    public function buildChange($firstObject, $secondObject, $propertyPath)
    {
        if (!$this->isDifferent($firstObject, $secondObject, $propertyPath)) {
            throw new RuntimeException("There are no differences between objects properties.");
        }

        $firstValue = $this->propertyAccessor->getValue($firstObject, $propertyPath);
        $secondValue = $this->propertyAccessor->getValue($secondObject, $propertyPath);

        return new Change($firstValue, $secondValue, $propertyPath);
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
