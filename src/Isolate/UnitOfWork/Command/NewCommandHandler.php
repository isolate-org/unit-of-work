<?php

namespace Isolate\UnitOfWork\Command;

interface NewCommandHandler
{
    /**
     * @param NewCommand $command
     */
    public function handle(NewCommand $command);
}
