<?php

namespace Coduo\UnitOfWork\Command;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;

class RemoveCommand
{
    private $object;

    /**
     * @param $object
     * @throws InvalidArgumentException
     */
    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf("Remove command require object \"%s\" type passed.", gettype($object)));
        }

        $this->object = $object;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
