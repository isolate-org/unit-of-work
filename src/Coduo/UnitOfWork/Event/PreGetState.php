<?php

namespace Coduo\UnitOfWork\Event;

use Symfony\Component\EventDispatcher\Event;

class PreGetState extends Event
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
