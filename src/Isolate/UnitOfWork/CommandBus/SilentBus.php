<?php

namespace Isolate\UnitOfWork\CommandBus;

use Isolate\UnitOfWork\Command\Command;
use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\CommandBus;
use Isolate\UnitOfWork\Entity\Definition\Repository;

class SilentBus implements CommandBus
{
    /**
     * @var Repository
     */
    private $definitions;

    /**
     * @param Repository $definitions
     */
    public function __construct(Repository $definitions)
    {
        $this->definitions = $definitions;
    }

    /**
     * @param Command $command
     */
    public function dispatch(Command $command)
    {
        $definition = $this->definitions->getDefinition($command->getEntity());

        if ($command instanceof NewCommand && $definition->hasNewCommandHandler()) {
            return $definition->getNewCommandHandler()->handle($command);
        }

        if ($command instanceof EditCommand && $definition->hasEditCommandHandler()) {
            return $definition->getEditCommandHandler()->handle($command);
        }

        if ($command instanceof RemoveCommand && $definition->hasRemoveCommandHandler()) {
            return $definition->getRemoveCommandHandler()->handle($command);
        }
    }
}
