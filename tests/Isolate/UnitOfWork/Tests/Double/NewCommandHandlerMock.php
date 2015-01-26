<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\NewCommandHandler;

class NewCommandHandlerMock implements  NewCommandHandler
{
    private $persistedEntities = [];

    /**
     * @param NewCommand $command
     */
    public function handle(NewCommand $command)
    {
        $this->persistedEntities[] = $command->getEntity();
    }

    public function entityWasPersisted($entity)
    {
        foreach ($this->persistedEntities as $persistedObject) {
            if ($persistedObject === $entity) {
                return true;
            }
        }

        return false;
    }
}
