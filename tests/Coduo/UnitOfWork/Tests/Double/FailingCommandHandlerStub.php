<?php

namespace Coduo\UnitOfWork\Tests\Double;

use Coduo\UnitOfWork\Command\EditCommand;
use Coduo\UnitOfWork\Command\EditCommandHandler;

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
