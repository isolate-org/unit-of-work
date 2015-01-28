<?php

namespace Isolate\UnitOfWork\Object;

interface Cloner
{
    /**
     * @param mixed $object
     * @return mixed
     */
    public function cloneObject($object);
}
