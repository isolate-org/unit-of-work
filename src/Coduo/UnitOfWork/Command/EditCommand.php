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
     * @param $object
     * @param ChangeSet $changeSet
     * @throws InvalidArgumentException
     */
    public function __construct($object, ChangeSet $changeSet)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf("Edit command require object \"%s\" type passed.", gettype($object)));
        }

        $this->object = $object;
        $this->changeSet = $changeSet;
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
}
