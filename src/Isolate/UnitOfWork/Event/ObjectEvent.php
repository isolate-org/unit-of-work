<?php

namespace Isolate\UnitOfWork\Event;

use Symfony\Component\EventDispatcher\Event;

trait ObjectEvent
{
    protected $object;

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
