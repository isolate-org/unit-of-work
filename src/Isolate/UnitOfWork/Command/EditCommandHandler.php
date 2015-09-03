<?php

namespace Isolate\UnitOfWork\Command;

/**
 * @api
 */
interface EditCommandHandler
{
    /**
     * @param EditCommand $command
     * 
     * @api
     */
    public function handle(EditCommand $command);
}
