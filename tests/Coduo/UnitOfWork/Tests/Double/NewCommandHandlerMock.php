<?php

namespace Coduo\UnitOfWork\Tests\Double;

use Coduo\UnitOfWork\Command\NewCommand;
use Coduo\UnitOfWork\Command\NewCommandHandler;

class NewCommandHandlerMock implements  NewCommandHandler
{
    private $persistedObjects = [];

    /**
     * @param NewCommand $command
     */
    public function handle(NewCommand $command)
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
