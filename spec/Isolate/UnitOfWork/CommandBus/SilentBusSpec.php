<?php

namespace spec\Isolate\UnitOfWork\CommandBus;

use Isolate\UnitOfWork\Command\EditCommand;
use Isolate\UnitOfWork\Command\EditCommandHandler;
use Isolate\UnitOfWork\Command\NewCommand;
use Isolate\UnitOfWork\Command\NewCommandHandler;
use Isolate\UnitOfWork\Command\RemoveCommand;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SilentBusSpec extends ObjectBehavior
{
    function let(Definition\Repository $definitions)
    {
        $definitions->getDefinition(Argument::type(EntityFake::getClassName()))
            ->willReturn($this->createEntityDefinition());

        $this->beConstructedWith($definitions);
    }

    function it_is_command_bus()
    {
        $this->shouldImplement('Isolate\UnitOfWork\CommandBus');
    }

    function it_dispatch_handler_for_new_command_if_exists(Definition\Repository $definitions, NewCommandHandler $handler)
    {
        $definition = $this->createEntityDefinition();
        $definition->setNewCommandHandler($handler->getWrappedObject());
        $entity = new EntityFake();
        $command = new NewCommand($entity);
        $definitions->getDefinition($entity)->willReturn($definition);

        $handler->handle($command)->willReturn(true);

        $this->dispatch($command)->shouldReturn(true);
    }

    function it_dispatch_handler_for_edit_command_if_exists(Definition\Repository $definitions, EditCommandHandler $handler)
    {
        $definition = $this->createEntityDefinition();
        $definition->setEditCommandHandler($handler->getWrappedObject());
        $entity = new EntityFake(1);
        $command = new EditCommand($entity, new ChangeSet());
        $definitions->getDefinition($entity)->willReturn($definition);

        $handler->handle($command)->willReturn(true);

        $this->dispatch($command)->shouldReturn(true);
    }

    function it_dispatch_handler_for_remove_command_if_exists(Definition\Repository $definitions, RemoveCommandHandler $handler)
    {
        $definition = $this->createEntityDefinition();
        $definition->setRemoveCommandHandler($handler->getWrappedObject());
        $entity = new EntityFake(1);
        $command = new RemoveCommand($entity);
        $definitions->getDefinition($entity)->willReturn($definition);

        $handler->handle($command)->willReturn(true);

        $this->dispatch($command)->shouldReturn(true);
    }

    function it_returns_null_when_there_is_no_handler_for_command_in_entity_definition()
    {
        $entity = new EntityFake(1);
        $command = new NewCommand($entity);
        $this->dispatch($command)->shouldReturn(null);
    }

    /**
     * @return Definition
     */
    private function createEntityDefinition()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $definition->addToObserved(new Definition\Property("firstName"));
        $definition->addToObserved(new Definition\Property("lastName"));

        return $definition;
    }
}
