<?php

namespace Isolate\UnitOfWork\Entity\Property;

use Isolate\UnitOfWork\Object\PropertyAccessor;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory;

final class PHPUnitValueComparer implements ValueComparer
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var Factory
     */
    private $comparatorFactory;
    
    public function __construct()
    {
        $this->comparatorFactory = new Factory();
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @param Property $property
     * @param $firstObject
     * @param $secondObject
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasDifferentValue(Property $property, $firstObject, $secondObject)
    {
        $this->validaObjects($firstObject, $secondObject);

        $firstValue = $this->propertyAccessor->getValue($firstObject, $property->getName());
        $secondValue = $this->propertyAccessor->getValue($secondObject, $property->getName());

        
        $comparator = $this->comparatorFactory->getComparatorFor($firstValue, $secondValue);

        try {
            $comparator->assertEquals($firstValue, $secondValue);
            return false;
        } catch (ComparisonFailure $exception) {
            return true;   
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
