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
     * @var int
     */
    private $totalNewEntities;

    /**
     * @param mixed $entity
     * @param int $totalNewEntities
     * @throws InvalidArgumentException
     */
    public function __construct($entity, $totalNewEntities)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(sprintf("New command require object \"%s\" type passed.", gettype($entity)));
        }

        if (!is_integer($totalNewEntities)) {
            throw new InvalidArgumentException(sprintf("Total new objects count must be valid integer."));
        }

        $this->entity = $entity;
        $this->totalNewEntities = $totalNewEntities;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return int
     */
    public function getTotalNewEntities()
    {
        return $this->totalNewEntities;
    }
}
