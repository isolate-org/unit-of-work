<?php

namespace Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;

/**
 * @api
 */
final class EditCommand implements Command
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
     * @param mixed $entity
     * @param ChangeSet $changeSet
     * @throws InvalidArgumentException
     */
    public function __construct($entity, ChangeSet $changeSet)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException(sprintf("Edit command require object \"%s\" type passed.", gettype($entity)));
        }

        $this->entity = $entity;
        $this->changeSet = $changeSet;
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

    /**
     * @return ChangeSet
     * 
     * @api
     */
    public function getChanges()
    {
        return $this->changeSet;
    }
}
