<?php

namespace Coduo\UnitOfWork\Tests\Double;

use Coduo\UnitOfWork\Command\RemoveCommand;
use Coduo\UnitOfWork\Command\RemoveCommandHandler;

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
