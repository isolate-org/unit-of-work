<?php

namespace Coduo\UnitOfWork\Command;

use Coduo\UnitOfWork\Exception\InvalidArgumentException;

final class RemoveCommand
{
    private $object;

    /**
     * @var int
     */
    private $totalRemovedObjects;

    /**
     * @param $object
     * @param int $totalRemovedObjects
     * @throws InvalidArgumentException
     */
    public function __construct($object, $totalRemovedObjects)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf("Remove command require object \"%s\" type passed.", gettype($object)));
        }

        if (!is_integer($totalRemovedObjects)) {
            throw new InvalidArgumentException(sprintf("Total removed objects count must be valid integer."));
        }

        $this->object = $object;
        $this->totalRemovedObjects = $totalRemovedObjects;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return int
     */
    public function getTotalRemovedObjects()
    {
        return $this->totalRemovedObjects;
    }
}
