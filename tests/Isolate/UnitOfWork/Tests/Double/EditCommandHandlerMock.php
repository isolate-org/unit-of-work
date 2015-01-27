<?php

namespace Isolate\UnitOfWork\Tests\Double;

use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\EditCommandHandler;

class EditCommandHandlerMock implements EditCommandHandler
{
    private $persistedEntities = [];

    private $persistedEntitiesChanges = [];

    /**
     * @param EditCommand $command
     */
    public function handle(EditCommand $command)
    {
        $this->persistedEntities[] = $command->getEntity();
        $this->persistedEntitiesChanges[] = $command->getChanges();
    }

    public function entityWasPersisted($entity)
    {
        foreach ($this->persistedEntities as $persistedEntity) {
            if ($persistedEntity === $entity) {
                return true;
            }
        }

        return false;
    }

    public function getPersistedEntityChanges($entity)
    {
        foreach ($this->persistedEntities as $index => $persistedEntity) {
            if ($persistedEntity === $entity) {
                return $this->persistedEntitiesChanges[$index];
            }
        }

        throw new \RuntimeException("Object was not handled");
    }
}
