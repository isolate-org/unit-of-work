<?php

namespace Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Command\EditCommandHandler;
use Isolate\UnitOfWork\Command\NewCommandHandler;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;
use Isolate\UnitOfWork\Entity\Definition\IdentificationStrategy;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;

/**
 * @api
 */
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
     * @var IdentificationStrategy
     */
    private $identificationStrategy;

    /**
     * @param ClassName $className
     * @param Identity $idDefinition
     * @param IdentificationStrategy $identificationStrategy
     */
    public function __construct(ClassName $className, Identity $idDefinition, IdentificationStrategy $identificationStrategy = null)
    {
        $this->className = $className;
        $this->idDefinition = $idDefinition;
        $this->observedProperties = [];
        $this->identificationStrategy = $identificationStrategy ?: new IdentificationStrategy\PropertyValue($idDefinition);
    }

    /**
     * @param Property[] $properties
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     * 
     * @api
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
     * 
     * @api
     */
    public function addToObserved(Property $property)
    {
        $this->validatePropertyPaths((string) $this->className, $this->idDefinition, [$property]);
        $this->observedProperties[] = $property;
        $this->observedProperties = array_unique($this->observedProperties);
    }

    /**
     * @return Property[]|array
     * 
     * @api
     */
    public function getObservedProperties()
    {
        return $this->observedProperties;
    }

    /**
     * @return ClassName
     * 
     * @api
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return Identity
     * 
     * @api
     */
    public function getIdDefinition()
    {
        return $this->idDefinition;
    }

    /**
     * @param $entity
     * @return bool
     * 
     * @api
     */
    public function fitsFor($entity)
    {
        return $this->className->isClassOf($entity);
    }

    /**
     * @param NewCommandHandler $commandHandler
     * 
     * @api
     */
    public function setNewCommandHandler(NewCommandHandler $commandHandler)
    {
        $this->newCommandHandler = $commandHandler;
    }

    /**
     * @return bool
     * 
     * @api
     */
    public function hasNewCommandHandler()
    {
        return isset($this->newCommandHandler);
    }

    /**
     * @return NewCommandHandler|null
     * 
     * @api
     */
    public function getNewCommandHandler()
    {
        return $this->newCommandHandler;
    }

    /**
     * @param EditCommandHandler $commandHandler
     * 
     * @api
     */
    public function setEditCommandHandler(EditCommandHandler $commandHandler)
    {
        $this->editCommandHandler = $commandHandler;
    }

    /**
     * @return bool
     * 
     * @api
     */
    public function hasEditCommandHandler()
    {
        return isset($this->editCommandHandler);
    }

    /**
     * @return EditCommandHandler|null
     * 
     * @api
     */
    public function getEditCommandHandler()
    {
        return $this->editCommandHandler;
    }

    /**
     * @param RemoveCommandHandler $commandHandler
     * 
     * @api
     */
    public function setRemoveCommandHandler(RemoveCommandHandler $commandHandler)
    {
        $this->removeCommandHandler = $commandHandler;
    }

    /**
     * @return bool
     * 
     * @api
     */
    public function hasRemoveCommandHandler()
    {
        return isset($this->removeCommandHandler);
    }

    /**
     * @return RemoveCommandHandler|null
     * 
     * @api
     */
    public function getRemoveCommandHandler()
    {
        return $this->removeCommandHandler;
    }

    /**
     * @return IdentificationStrategy|IdentificationStrategy
     *
     * @api
     */
    public function getIdentityStrategy()
    {
        return $this->identificationStrategy;
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
