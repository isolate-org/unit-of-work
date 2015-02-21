<?php

namespace Isolate\UnitOfWork\Object\SnapshotMaker\Adapter\DeepCopy;

use DeepCopy\DeepCopy;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Object\SnapshotMaker as BaseSnapshotMaker;

class SnapshotMaker implements BaseSnapshotMaker
{
    /**
     * @var DeepCopy
     */
    private $cloner;

    public function __construct()
    {
        $this->cloner = new DeepCopy();
    }

    /**
     * @param mixed $object
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function makeSnapshotOf($object)
    {
        return $this->cloner->copy($object);
    }
}
