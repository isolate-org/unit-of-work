<?php

namespace Isolate\UnitOfWork\Entity\Value;

use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Entity\Value\Change\ScalarChange;

/**
 * @api
 */
class ChangeSet extends \ArrayObject
{
    /**
     * @param $propertyName
     * @return bool
     * 
     * @api
     */
    public function hasChangeFor($propertyName)
    {
        foreach ($this->getIterator() as $change) {
            /* @var ScalarChange $change */
            if ($change->isFor($propertyName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $propertyName
     * @return Change
     * @throws RuntimeException
     * 
     * @api
     */
    public function getChangeFor($propertyName)
    {
        foreach ($this->getIterator() as $change) {
            /* @var \Isolate\UnitOfWork\Entity\Value\Change\ScalarChange $change */
            if ($change->isFor($propertyName)) {
                return $change;
            }
        }

        throw new RuntimeException(sprintf("There are no changes for \"%s\" property.", $propertyName));
    }

    /**
     * @param array $properties
     * @return bool
     * 
     * @api
     */
    public function hasChangesForAny(array $properties = [])
    {
        foreach ($properties as $propertyName) {
            if ($this->hasChangeFor($propertyName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ScalarChange[]
     * 
     * @api
     */
    public function all()
    {
        return $this->getIterator();
    }

    /**
     * @param mixed $index
     *
     * @return Change
     */
    public function offsetGet($index)
    {
        parent::offsetGet($index);
    }
}
