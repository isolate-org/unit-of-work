<?php

namespace Isolate\UnitOfWork\Entity\Value\Change;

use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Value\Change;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;

final class EditedEntity implements Change
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
     * @var ChangeSet
     */
    private $changeSet;

    /**
     * @param Property $property
     * @param ChangeSet $changeSet
     * @param $originValue
     * @param $newValue
     */
    public function __construct(Property $property, ChangeSet $changeSet, $originValue, $newValue)
    {
        $this->property = $property;
        $this->originValue = $originValue;
        $this->newValue = $newValue;
        $this->changeSet = $changeSet;
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
     * @return ChangeSet
     * 
     * @api
     */
    public function getChangeSet()
    {
        return $this->changeSet;
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
