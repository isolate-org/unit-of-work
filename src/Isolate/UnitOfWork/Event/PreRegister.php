<?php

namespace Isolate\UnitOfWork\Event;

use Symfony\Component\EventDispatcher\Event;

class PreRegister extends Event
{
    use EntityEvent;

    /**
     * @param $entity
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}
