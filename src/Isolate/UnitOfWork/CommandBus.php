<?php

namespace Isolate\UnitOfWork;

use Isolate\UnitOfWork\Command\Command;

/**
 * @api
 */
interface CommandBus
{
    /**
     * @param Command $command
     * 
     * @api
     */
    public function dispatch(Command $command);
}
