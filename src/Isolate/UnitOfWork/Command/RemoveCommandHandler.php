<?php

namespace Isolate\UnitOfWork\Command;

interface RemoveCommandHandler
{
    /**
     * @param RemoveCommand $command
     */
    public function handle(RemoveCommand $command);
}
