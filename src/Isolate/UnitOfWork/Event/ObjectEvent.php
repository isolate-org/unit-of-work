<?php

namespace Isolate\UnitOfWork\Event;

trait ObjectEvent
{
    protected $object;

    /**
     * @param $newObject
     */
    public function replaceObject($newObject)
    {
        $this->object = $newObject;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}
