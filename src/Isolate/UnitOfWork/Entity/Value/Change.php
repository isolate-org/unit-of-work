<?php

namespace Isolate\UnitOfWork\Entity\Value;

use Isolate\UnitOfWork\Entity\Definition\Property;

final class Change
{
    /**
     * @var mixed
     */
    private $originValue;

    /**
     * @var mixed
     */
    private $newValue;

    /**
     * @var Property
     */
    private $property;

    /**
     * @param Property $property
     * @param $originValue
     * @param $newValue
     */
    public function __construct(Property $property, $originValue, $newValue)
    {
        $this->property = $property;
        $this->originValue = $originValue;
        $this->newValue = $newValue;
    }

    /**
     * @return mixed
     */
    public function getOriginValue()
    {
        return $this->originValue;
    }

    /**
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param $propertyName
     * @return bool
     */
    public function isFor($propertyName)
    {
        return $this->property->getName() === $propertyName;
    }
}
