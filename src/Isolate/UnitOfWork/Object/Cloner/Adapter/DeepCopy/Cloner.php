<?php

namespace Isolate\UnitOfWork\Object\Cloner\Adapter\DeepCopy;

use DeepCopy\DeepCopy;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Object\Cloner as BaseCloner;

class Cloner implements BaseCloner
{
    /**
     * @var DeepCopy
     */
    private $cloner;

    public function __construct()
    {
        $this->cloner = new DeepCopy();
    }

    /**
     * @param mixed $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function cloneObject($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException("Only object can be cloned.");
        }

        return $this->cloner->copy($object);
    }
}
