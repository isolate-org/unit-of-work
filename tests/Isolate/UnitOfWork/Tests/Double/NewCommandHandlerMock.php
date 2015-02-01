<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\NewCommandHandler;
use Isolate\UnitOfWork\Object\PropertyAccessor;

class NewCommandHandlerMock implements  NewCommandHandler
{
    private $persistedEntities = [];

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = new PropertyAccessor();
    }

    /**
     * @param NewCommand $command
     */
    public function handle(NewCommand $command)
    {
        $this->persistedEntities[] = $command->getEntity();

        //After persisting entity we need to give it some unique identity
        $this->propertyAccessor->setValue($command->getEntity(), "id", time());
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
