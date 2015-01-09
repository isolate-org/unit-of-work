<?php

namespace Coduo\UnitOfWork;

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
}
