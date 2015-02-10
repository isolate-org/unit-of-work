<?php

namespace Isolate\UnitOfWork\Object;

interface SnapshotMaker
{
    /**
     * @param mixed $object
     * @return mixed
     */
    public function makeSnapshotOf($object);
}
