<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Property\ValueComparer;

class Comparer
{
    /**
     * @var ValueComparer
     */
    private $propertyValueComparer;

    public function __construct()
    {
        $this->propertyValueComparer = new ValueComparer();
    }

    /**
     * @param Definition $entityDefinition
     * @param $firstEntity
     * @param $secondEntity
     * @return bool
     */
    public function areEqual(Definition $entityDefinition, $firstEntity, $secondEntity)
    {
        foreach ($entityDefinition->getObservedProperties() as $property) {
            if ($this->propertyValueComparer->hasDifferentValue($property, $firstEntity, $secondEntity)) {
                return false;
            }
        }

        return true;
    }
}
