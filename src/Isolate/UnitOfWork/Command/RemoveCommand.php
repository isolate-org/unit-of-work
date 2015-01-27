<?php

namespace Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;

final class RemoveCommand
{
    /**
     * @var mixed
     */
    private $entity;

    /**
     * @var int
     */
    private $totalRemovedObjects;

    /**
     * @param mixed $entity
     * @param int $totalRemovedEntities
     * @throws InvalidArgumentException
     */
    public function __construct($entity, $totalRemovedEntities)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(sprintf("Remove command require object \"%s\" type passed.", gettype($entity)));
        }

        if (!is_integer($totalRemovedEntities)) {
            throw new InvalidArgumentException(sprintf("Total removed objects count must be valid integer."));
        }

        $this->entity = $entity;
        $this->totalRemovedObjects = $totalRemovedEntities;
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
    public function getTotalRemovedObjects()
    {
        return $this->totalRemovedObjects;
    }
}
