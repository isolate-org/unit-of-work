<?php

namespace Isolate\UnitOfWork\Object;

/**
 * @api
 */
interface SnapshotMaker
{
    /**
     * @param mixed $object
     * @return mixed
     * 
     * @api
     */
    public function makeSnapshotOf($object);
}
