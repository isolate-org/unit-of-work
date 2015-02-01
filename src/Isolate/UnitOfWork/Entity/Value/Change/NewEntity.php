<?php

namespace Isolate\UnitOfWork\Entity\Value\Change;

use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Value\Change;

final class NewEntity implements Change
{
    /**
     * @var mixed
     */
    private $newValue;

    /**
     * @var Property
     */
    private $property;


    /**
     * @var boolean
     */
    private $isPersisted;

    /**
     * @param Property $property
     * @param $newValue
     * @param boolean $isPersisted
     */
    public function __construct(Property $property, $newValue, $isPersisted)
    {
        $this->property = $property;
        $this->newValue = $newValue;
        $this->isPersisted = (boolean) $isPersisted;
    }

    /**
     * @return mixed
     */
    public function getOriginValue()
    {
        return null;
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
     * @return boolean
     */
    public function isPersisted()
    {
        return $this->isPersisted;
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
