<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Command\EditCommandHandler;
use Isolate\UnitOfWork\Command\NewCommandHandler;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;

class Definition
{
    /**
     * @var ClassName
     */
    private $className;

    /**
     * @var Identity
     */
    private $idDefinition;

    /**
     * @var NewCommandHandler|null
     */
    private $newCommandHandler;

    /**
     * @var EditCommandHandler|null
     */
    private $editCommandHandler;

    /**
     * @var RemoveCommandHandler|null
     */
    private $removeCommandHandler;

    /**
     * @var array
     */
    private $observedProperties;

    /**
     * @param ClassName $className
     * @param Identity $idDefinition
     * @throws InvalidArgumentException
     */
    public function __construct(ClassName $className, Identity $idDefinition)
    {
        $this->className = $className;
        $this->idDefinition = $idDefinition;
        $this->observedProperties = [];
    }

    /**
     * @param Property[] $properties
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     */
    public function setObserved(array $properties)
    {
        foreach ($properties as $property) {
            if (!$property instanceof Property) {
                throw new InvalidArgumentException("Each observed property needs to be an instance of Property");
            }
        }
        $this->validatePropertyPaths((string) $this->className, $this->idDefinition, $properties);
        $this->observedProperties = array_unique($properties);
    }

    /**
     * @param Property $property
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     */
    public function addToObserved(Property $property)
    {
        $this->validatePropertyPaths((string) $this->className, $this->idDefinition, [$property]);
        $this->observedProperties[] = $property;
        $this->observedProperties = array_unique($this->observedProperties);
    }

    /**
     * @return Property[]|array
     */
    public function getObservedProperties()
    {
        return $this->observedProperties;
    }

    /**
     * @return ClassName
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return Identity
     */
    public function getIdDefinition()
    {
        return $this->idDefinition;
    }

    /**
     * @param $entity
     * @return bool
     */
    public function fitsFor($entity)
    {
        return $this->className->isClassOf($entity);
    }

    /**
     * @param NewCommandHandler $commandHandler
     */
    public function setNewCommandHandler(NewCommandHandler $commandHandler)
    {
        $this->newCommandHandler = $commandHandler;
    }

    /**
     * @return bool
     */
    public function hasNewCommandHandler()
    {
        return isset($this->newCommandHandler);
    }

    /**
     * @return NewCommandHandler|null
     */
    public function getNewCommandHandler()
    {
        return $this->newCommandHandler;
    }

    /**
     * @param EditCommandHandler $commandHandler
     */
    public function setEditCommandHandler(EditCommandHandler $commandHandler)
    {
        $this->editCommandHandler = $commandHandler;
    }

    /**
     * @return bool
     */
    public function hasEditCommandHandler()
    {
        return isset($this->editCommandHandler);
    }

    /**
     * @return EditCommandHandler|null
     */
    public function getEditCommandHandler()
    {
        return $this->editCommandHandler;
    }

    /**
     * @param RemoveCommandHandler $commandHandler
     */
    public function setRemoveCommandHandler(RemoveCommandHandler $commandHandler)
    {
        $this->removeCommandHandler = $commandHandler;
    }

    /**
     * @return bool
     */
    public function hasRemoveCommandHandler()
    {
        return isset($this->removeCommandHandler);
    }

    /**
     * @return RemoveCommandHandler|null
     */
    public function getRemoveCommandHandler()
    {
        return $this->removeCommandHandler;
    }

    /**
     * @param $className
     * @param Identity $idDefinition
     * @param array $observedProperties
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     */
    private function validatePropertyPaths($className, Identity $idDefinition, array $observedProperties)
    {
        $reflection = new \ReflectionClass($className);
        foreach ($observedProperties as $property) {
            $propertyName = $property->getName();
            if ($idDefinition->isEqual($propertyName)) {
                throw new InvalidArgumentException("Id definition property path can't be between observer properties.");
            }

            if (!$reflection->hasProperty($propertyName)) {
                throw new NotExistingPropertyException(sprintf(
                    "Property \"%s\" does not exists in \"%s\" class.",
                    $propertyName,
                    $className
                ));
            }
        }
    }
}
