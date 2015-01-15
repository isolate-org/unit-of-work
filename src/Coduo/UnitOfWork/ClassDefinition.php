<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\EditCommandHandler;
use Coduo\UnitOfWork\Command\NewCommandHandler;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;

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
     * @var array
     */
    private $observedPropertyPaths;

    /**
     * @param string $className
     * @param IdDefinition $idDefinition
     * @param $observedPropertyPaths
     * @throws InvalidArgumentException
     */
    public function __construct($className, IdDefinition $idDefinition, array $observedPropertyPaths)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException("Class name must be a valid string.");
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf("Class \"%s\" does not exists.", $className));
        }

        $this->validatePropertyPaths($idDefinition, $observedPropertyPaths);

        $this->className = $className;
        $this->idDefinition = $idDefinition;
        $this->observedPropertyPaths = $observedPropertyPaths;
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
     * @return array
     */
    public function getObservedPropertyPaths()
    {
        return $this->observedPropertyPaths;
    }

    /**
     * @param IdDefinition $idDefinition
     * @param array $observedPropertyPaths
     * @throws InvalidArgumentException
     */
    private function validatePropertyPaths(IdDefinition $idDefinition, array $observedPropertyPaths)
    {
        if (!count($observedPropertyPaths)) {
            throw new InvalidArgumentException("You need to observe at least one property.");
        }

        foreach ($observedPropertyPaths as $propertyPath) {
            if ($idDefinition->hasSame($propertyPath)) {
                throw new InvalidArgumentException("Id definition property path can't be between observer property paths.");
            }
        }
    }
}
