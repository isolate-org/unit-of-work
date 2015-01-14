<?php

namespace Coduo\UnitOfWork\Command;

interface EditCommandHandler
{
    /**
     * @param EditCommand $command
     */
    public function handle(EditCommand $command);
}
