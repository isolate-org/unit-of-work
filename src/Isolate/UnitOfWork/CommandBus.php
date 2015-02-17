<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Command\Command;

interface CommandBus
{
    /**
     * @param Command $command
     */
    public function dispatch(Command $command);
}
