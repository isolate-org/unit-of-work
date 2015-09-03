<?php

namespace Isolate\UnitOfWork\Command;

/**
 * @api
 */
interface RemoveCommandHandler
{
    /**
     * @param RemoveCommand $command
     * 
     * @api
     */
    public function handle(RemoveCommand $command);
}
