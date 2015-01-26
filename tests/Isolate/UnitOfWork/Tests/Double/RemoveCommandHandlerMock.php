<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;

class RemoveCommandHandlerMock implements RemoveCommandHandler
{
    private $removedEntities = [];

    /**
     * @param RemoveCommand $command
     */
    public function handle(RemoveCommand $command)
    {
        $this->removedEntities[] = $command->getEntity();
    }

    public function entityWasRemoved($entity)
    {
        foreach ($this->removedEntities as $persistedObject) {
            if ($persistedObject === $entity) {
                return true;
            }
        }

        return false;
    }
}
