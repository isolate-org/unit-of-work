<?php

namespace Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;

final class EditCommand
{
    /**
     * @var mixed
     */
    private $entity;

    /**
     * @var ChangeSet
     */
    private $changeSet;

    /**
     * @var int
     */
    private $totalEditedEntities;

    /**
     * @param mixed $entity
     * @param ChangeSet $changeSet
     * @param int $totalEditedEntities
     * @throws InvalidArgumentException
     */
    public function __construct($entity, ChangeSet $changeSet, $totalEditedEntities)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(sprintf("Edit command require object \"%s\" type passed.", gettype($entity)));
        }

        if (!is_integer($totalEditedEntities)) {
            throw new InvalidArgumentException(sprintf("Total edited objects count must be valid integer."));
        }

        $this->entity = $entity;
        $this->changeSet = $changeSet;
        $this->totalEditedEntities = $totalEditedEntities;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return ChangeSet
     */
    public function getChanges()
    {
        return $this->changeSet;
    }

    /**
     * @return int
     */
    public function getTotalEditedEntities()
    {
        return $this->totalEditedEntities;
    }
}
