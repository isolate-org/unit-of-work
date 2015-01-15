<?php

namespace Coduo\UnitOfWork\Command;

interface RemoveCommandHandler
{
    /**
     * @param RemoveCommand $command
     */
    public function handle(RemoveCommand $command);
}
