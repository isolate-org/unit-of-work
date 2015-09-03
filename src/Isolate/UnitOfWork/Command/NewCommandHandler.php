<?php

namespace Isolate\UnitOfWork\Command;

/**
 * @api
 */
interface NewCommandHandler
{
    /**
     * @param NewCommand $command
     * 
     * @api
     */
    public function handle(NewCommand $command);
}
