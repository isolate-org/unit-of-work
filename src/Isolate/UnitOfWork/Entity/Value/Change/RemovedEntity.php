<?php

namespace Isolate\UnitOfWork\Entity\Value\Change;

use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Value\Change;

final class RemovedEntity implements Change
{
    /**
     * @var mixed
     */
    private $originValue;

    /**
     * @var Property
     */
    private $property;

    /**
     * @param Property $property
     * @param $originValue
     */
    public function __construct(Property $property, $originValue)
    {
        $this->property = $property;
        $this->originValue = $originValue;
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
        return null;
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
