<?php

namespace Isolate\UnitOfWork\Event;

use Symfony\Component\EventDispatcher\Event;

class PreRegister extends Event
{
    use ObjectEvent;

    /**
     * @param $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }
}
