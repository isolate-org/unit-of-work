<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\EditCommandHandler;
use Coduo\UnitOfWork\Command\NewCommandHandler;
use Coduo\UnitOfWork\Command\RemoveCommandHandler;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\Exception\NotExistingPropertyException;

class ClassDefinition
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var IdDefinition
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
     * @param string $className
     * @param IdDefinition $idDefinition
     * @param $observedProperties
     * @throws InvalidArgumentException
     */
    public function __construct($className, IdDefinition $idDefinition, array $observedProperties)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException("Class name must be a valid string.");
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf("Class \"%s\" does not exists.", $className));
        }

        $this->validatePropertyPaths($className, $idDefinition, $observedProperties);

        $this->className = $className;
        $this->idDefinition = $idDefinition;
        $this->observedProperties = $observedProperties;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return IdDefinition
     */
    public function getIdDefinition()
    {
        return $this->idDefinition;
    }

    /**
     * @param $object
     * @return bool
     */
    public function fitsFor($object)
    {
        return is_a($object, $this->className);
    }

    /**
     * @param NewCommandHandler $commandHandler
     */
    public function addNewCommandHandler(NewCommandHandler $commandHandler)
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
    public function addEditCommandHandler(EditCommandHandler $commandHandler)
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
    public function addRemoveCommandHandler(RemoveCommandHandler $commandHandler)
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
     * @return array
     */
    public function getObservedProperties()
    {
        return $this->observedProperties;
    }

    /**
     * @param $className
     * @param IdDefinition $idDefinition
     * @param array $observedProperties
     * @throws InvalidArgumentException
     * @throws NotExistingPropertyException
     */
    private function validatePropertyPaths($className, IdDefinition $idDefinition, array $observedProperties)
    {
        $reflection = new \ReflectionClass($className);
        foreach ($observedProperties as $propertyName) {
            if ($idDefinition->itFits($propertyName)) {
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
