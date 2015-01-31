<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Property\ValueComparer;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Object\PropertyAccessor;
use Isolate\UnitOfWork\Entity\Value\Change;
use Isolate\UnitOfWork\Exception\RuntimeException;

final class ChangeBuilder
{
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @var ValueComparer
     */
    private $propertyValueComparer;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->propertyValueComparer = new ValueComparer();
    }

    /**
     * @param Definition $entityDefinition
     * @param $firstObject
     * @param $secondObject
     * @return ChangeSet
     * @throws RuntimeException
     */
    public function buildChanges(Definition $entityDefinition, $firstObject, $secondObject)
    {
        $changes = [];
        foreach ($entityDefinition->getObservedProperties() as $property) {
            if ($this->propertyValueComparer->hasDifferentValue($property, $firstObject, $secondObject)) {
                $firstValue = $this->propertyAccessor->getValue($firstObject, $property->getName());
                $secondValue = $this->propertyAccessor->getValue($secondObject, $property->getName());
                $changes[] = new Change($property, $firstValue, $secondValue);
            }
        }

        return new ChangeSet($changes);
    }
}
