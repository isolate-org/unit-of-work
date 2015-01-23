<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\EditCommandHandler;

class FailingCommandHandlerStub implements EditCommandHandler
{
    /**
     * @param EditCommand $command
     */
    public function handle(EditCommand $command)
    {
        return false;
    }
}
