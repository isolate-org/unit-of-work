<?php

namespace Isolate\UnitOfWork\Entity\Value\Change;

use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Value\Change;
use Isolate\UnitOfWork\Entity\Value\Change\EditedEntity;
use Isolate\UnitOfWork\Entity\Value\Change\NewEntity;
use Isolate\UnitOfWork\Entity\Value\Change\RemovedEntity;

final class AssociatedCollection implements Change
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
     * @var array
     */
    private $changes;

    /**
     * @param Property $property
     * @param $originValue
     * @param $newValue
     * @param array $changes
     */
    public function __construct(Property $property, $originValue, $newValue, array $changes)
    {
        $this->property = $property;
        $this->originValue = $originValue;
        $this->newValue = $newValue;
        $this->changes = $changes;
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
     * @return array|[]NewEntity
     */
    public function getNewElements()
    {
        $new = [];
        foreach ($this->changes as $change) {
            if ($change instanceof NewEntity) {
                $new[] = $change;
            }
        }

        return $new;
    }

    /**
     * @return array|[]NewEntity
     */
    public function getRemovedElements()
    {
        $removed = [];
        foreach ($this->changes as $change) {
            if ($change instanceof RemovedEntity) {
                $removed[] = $change;
            }
        }

        return $removed;
    }

    /**
     * @return array|[]NewEntity
     */
    public function getEditedElements()
    {
        $edited = [];
        foreach ($this->changes as $change) {
            if ($change instanceof EditedEntity) {
                $edited[] = $change;
            }
        }

        return $edited;
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
