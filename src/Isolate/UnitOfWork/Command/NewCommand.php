<?php

namespace Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

final class NewCommand
{
    /**
     * @var mixed
     */
    private $entity;

    /**
     * @param mixed $entity
     * @throws InvalidArgumentException
     */
    public function __construct($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(sprintf("New command require object \"%s\" type passed.", gettype($entity)));
        }

        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
