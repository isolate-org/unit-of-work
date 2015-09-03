<?php

namespace Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

/**
 * @api
 */
final class NewCommand implements Command
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
     * 
     * @api
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
