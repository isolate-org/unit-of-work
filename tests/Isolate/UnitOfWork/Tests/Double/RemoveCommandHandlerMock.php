<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;

class RemoveCommandHandlerMock implements RemoveCommandHandler
{
    private $persistedObjects = [];

    /**
     * @param RemoveCommand $command
     */
    public function handle(RemoveCommand $command)
    {
        $this->persistedObjects[] = $command->getObject();
    }

    public function objectWasPersisted($object)
    {
        foreach ($this->persistedObjects as $persistedObject) {
            if ($persistedObject === $object) {
                return true;
            }
        }

        return false;
    }
}
