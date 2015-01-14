<?php

namespace Coduo\UnitOfWork\Command;

interface NewCommandHandler
{
    /**
     * @param NewCommand $command
     */
    public function handle(NewCommand $command);
}
