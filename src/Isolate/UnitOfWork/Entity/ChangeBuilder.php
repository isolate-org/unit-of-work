<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\Definition\Association;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Property\ValueComparer;
use Isolate\UnitOfWork\Entity\Value\Change\AssociatedCollection;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Entity\Value\Change\EditedEntity;
use Isolate\UnitOfWork\Entity\Value\Change\NewEntity;
use Isolate\UnitOfWork\Entity\Value\Change\RemovedEntity;
use Isolate\UnitOfWork\Object\PropertyAccessor;
use Isolate\UnitOfWork\Entity\Value\Change\ScalarChange;
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

    /**
     * @var InformationPoint
     */
    private $informationPoint;

    /**
     * @param InformationPoint $informationPoint
     */
    public function __construct(InformationPoint $informationPoint)
    {
        $this->propertyAccessor = new PropertyAccessor();
        $this->propertyValueComparer = new ValueComparer();
        $this->informationPoint = $informationPoint;
    }

    /**
     * @param $oldEntity
     * @param $newEntity
     * @return ChangeSet
     * @throws RuntimeException
     */
    public function buildChanges($oldEntity, $newEntity)
    {
        $changes = [];
        $entityDefinition = $this->informationPoint->getDefinition($oldEntity);
        foreach ($entityDefinition->getObservedProperties() as $property) {
            if ($this->isDifferent($property, $oldEntity, $newEntity)) {
                $oldValue = $this->propertyAccessor->getValue($oldEntity, $property->getName());
                $newValue = $this->propertyAccessor->getValue($newEntity, $property->getName());

                $changes[] = $this->buildChange($property, $oldValue, $newValue);
            }
        }

        return new ChangeSet($changes);
    }

    /**
     * @param Property $property
     * @param $oldEntity
     * @param $newEntity
     * @return bool
     */
    private function isDifferent(Property $property, $oldEntity, $newEntity)
    {
        return $this->propertyValueComparer->hasDifferentValue($property, $newEntity, $oldEntity);
    }

    /**
     * @param Property $property
     * @param $oldValue
     * @param $newValue
     * @return \Isolate\UnitOfWork\Entity\Value\Change\ScalarChange
     * @throws RuntimeException
     */
    private function buildChange(Property $property, $oldValue, $newValue)
    {
        if ($property->isAssociated()) {
            $association = $property->getAssociation();
            switch ($association->getType()) {
                case Association::TO_SINGLE_ENTITY:
                    return $this->buildAssociationToSingleEntityChange($property, $oldValue, $newValue);
                    break;
                case Association::TO_MANY_ENTITIES;
                    return $this->buildAssociationToManyEntitiesChange($property, $oldValue, $newValue);
                    break;
            }
        }

        return new ScalarChange($property, $oldValue, $newValue);
    }

    /**
     * @param Property $property
     * @param $oldValue
     * @param $newValue
     * @return NewEntity|RemovedEntity|null
     * @throws RuntimeException
     */
    private function buildAssociationToSingleEntityChange(Property $property, $oldValue, $newValue)
    {
        if (is_null($newValue)) {
            return new RemovedEntity($property, $oldValue);
        }

        if (is_null($oldValue)) {
            $this->validateAssociatedEntity($property, $newValue);

            return new NewEntity($property, $newValue, $this->informationPoint->isPersisted($newValue));
        }

        return new EditedEntity(
            $property,
            $this->buildChanges($oldValue, $newValue),
            $oldValue,
            $newValue
        );
    }

    /**
     * @param Property $property
     * @param $oldValue
     * @param $newValue
     * @return AssociatedCollection
     * @throws RuntimeException
     */
    private function buildAssociationToManyEntitiesChange(Property $property, $oldValue, $newValue)
    {
        if (!$this->isTraversableArray($newValue)) {
            throw new RuntimeException(
                sprintf(
                    "Property \"%s\" is marked as associated with many entities and require new value to be traversable collection.",
                    $property->getName()
                )
            );
        }


        $oldPersistedArray = $this->toPersistedArray($oldValue);
        $newPersistedArray = [];
        $changes = [];

        foreach ($newValue as $newElement) {
            $this->validateAssociatedEntity($property, $newElement);

            if (!$this->informationPoint->isPersisted($newElement)) {
                $changes[] = new NewEntity($property, $newElement, false);
                continue;
            }

            $identity = $this->informationPoint->getIdentity($newElement);
            $newPersistedArray[$identity] = $newElement;

            if (array_key_exists($identity, $oldPersistedArray)) {
                $oldElement = $oldPersistedArray[$identity];
                $changeSet = $this->buildChanges($oldElement, $newElement);

                if ($changeSet->count()) {
                    $changes[] = new EditedEntity($property, $changeSet, $oldElement, $newElement);
                }

                continue;
            }

            $changes[] = new NewEntity($property, $newElement, true);
        }

        foreach ($oldPersistedArray as $identity => $oldElement) {
            if (!array_key_exists($identity, $newPersistedArray)) {
                $changes[] = new RemovedEntity($property, $oldElement);
            }
        }

        return new AssociatedCollection($property, $oldValue, $newValue, $changes);
    }

    /**
     * @param $traversableArray
     * @return array
     */
    private function toPersistedArray($traversableArray)
    {
        if (!$this->isTraversableArray($traversableArray)) {
            return [];
        }

        $result = [];
        foreach ($traversableArray as $valueElement) {
            $result[$this->informationPoint->getIdentity($valueElement)] = $valueElement;
        }

        return $result;
    }

    /**
     * @param Property $property
     * @param $newElement
     * @throws RuntimeException
     */
    private function validateAssociatedEntity(Property $property, $newElement)
    {
        if (!is_object($newElement) || !$property->getAssociation()->getTargetClassName()->isClassOf($newElement)) {
            throw new RuntimeException(
                sprintf(
                    "Property \"%s\" expects instanceof \"%s\" as a value.",
                    $property->getName(),
                    (string) $property->getAssociation()->getTargetClassName()
                )
            );
        }
    }

    /**
     * @param $newValue
     * @return bool
     */
    private function isTraversableArray($newValue)
    {
        return is_array($newValue) || ($newValue instanceof \Traversable && $newValue instanceof \ArrayAccess);
    }
}
