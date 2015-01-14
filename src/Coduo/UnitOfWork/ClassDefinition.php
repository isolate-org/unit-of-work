<?php

namespace Coduo\UnitOfWork;

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
     * @param string $className
     * @param IdDefinition $idDefinition
     * @throws InvalidArgumentException
     */
    public function __construct($className, IdDefinition $idDefinition)
    {
        if (!is_string($className)) {
            throw new InvalidArgumentException("Class name must be a valid string.");
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf("Class \"%s\" does not exists.", $className));
        }

        $this->className = $className;
        $this->idDefinition = $idDefinition;
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
}
