<?php

namespace Isolate\UnitOfWork\Entity\Value;

use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Entity\Value\Change;

class ChangeSet extends \ArrayObject
{
    /**
     * @param $propertyName
     * @return bool
     */
    public function hasChangeFor($propertyName)
    {
        foreach ($this->getIterator() as $change) {
            /* @var Change $change */
            if ($change->isFor($propertyName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $propertyName
     * @return bool
     * @throws RuntimeException
     */
    public function getChangeFor($propertyName)
    {
        foreach ($this->getIterator() as $change) {
            /* @var Change $change */
            if ($change->isFor($propertyName)) {
                return $change;
            }
        }

        throw new RuntimeException(sprintf("There are no changes for \"%s\" property.", $propertyName));
    }

    /**
     * @return Change[]
     */
    public function all()
    {
        return $this->getIterator();
    }
}
