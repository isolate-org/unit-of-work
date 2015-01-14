<?php

namespace Coduo\UnitOfWork;

use Coduo\UnitOfWork\Exception\RuntimeException;

class ChangeSet extends \ArrayObject
{
    /**
     * @param $propertyPath
     * @return bool
     */
    public function hasChangeFor($propertyPath)
    {
        foreach ($this->getIterator() as $change) {
            /* @var Change $change */
            if ($change->hasSame($propertyPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $propertyPath
     * @return bool
     * @throws RuntimeException
     */
    public function getChangeFor($propertyPath)
    {
        foreach ($this->getIterator() as $change) {
            /* @var Change $change */
            if ($change->hasSame($propertyPath)) {
                return $change;
            }
        }

        throw new RuntimeException(sprintf("There are not changes for \"%s\" property path.", $propertyPath));
    }

    /**
     * @return Change[]
     */
    public function all()
    {
        return $this->getIterator();
    }
}
