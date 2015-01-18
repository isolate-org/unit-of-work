<?php

namespace Coduo\UnitOfWork\Command;

use Coduo\UnitOfWork\ChangeSet;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;

final class EditCommand
{
    /**
     * @var mixed
     */
    private $object;

    /**
     * @var ChangeSet
     */
    private $changeSet;

    /**
     * @var int
     */
    private $totalEditedObjects;

    /**
     * @param mixed $object
     * @param ChangeSet $changeSet
     * @param int $totalEditedObjects
     * @throws InvalidArgumentException
     */
    public function __construct($object, ChangeSet $changeSet, $totalEditedObjects)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf("Edit command require object \"%s\" type passed.", gettype($object)));
        }

        if (!is_integer($totalEditedObjects)) {
            throw new InvalidArgumentException(sprintf("Total edited objects count must be valid integer."));
        }

        $this->object = $object;
        $this->changeSet = $changeSet;
        $this->totalEditedObjects = $totalEditedObjects;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
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
    public function getTotalEditedObjects()
    {
        return $this->totalEditedObjects;
    }
}
