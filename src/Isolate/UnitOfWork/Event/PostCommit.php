<?php

namespace Isolate\UnitOfWork\Event;

use Symfony\Component\EventDispatcher\Event;

class PostCommit extends Event
{
    /**
     * @var
     */
    private $successful;

    /**
     * @param $isSuccessful
     */
    public function __construct($isSuccessful = true)
    {
        $this->successful = (boolean) $isSuccessful;
    }

    public function isSuccessful()
    {
        return $this->successful;
    }
}
