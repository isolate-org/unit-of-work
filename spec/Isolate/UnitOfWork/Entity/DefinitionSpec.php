<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Command\EditCommandHandler;
use Isolate\UnitOfWork\Command\NewCommandHandler;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefinitionSpec extends ObjectBehavior
{
    function it_throws_exception_when_class_id_is_between_observed_properties()
    {
        $className = new ClassName(EntityFake::getClassName());
        $this->beConstructedWith($className, new Identity("id"));

        $this->shouldThrow(new InvalidArgumentException("Id definition property path can't be between observer properties."))
            ->during("addToObserved", [new Property("id")]);
    }

    function it_force_observed_properties_to_be_unique()
    {
        $className = new ClassName(EntityFake::getClassName());
        $this->beConstructedWith($className, new Identity("id"));

        $this->setObserved([new Property("firstName"), new Property("firstName")]);

        $this->getObservedProperties()->shouldHaveCount(1);

        $this->setObserved([]);
        $this->addToObserved(new Property("firstName"));
        $this->addToObserved(new Property("firstName"));
        $this->getObservedProperties()->shouldHaveCount(1);
    }

    function it_returns_class_name()
    {
        $className = new ClassName(EntityFake::getClassName());
        $this->beConstructedWith($className, new Identity("id"));
        $this->getClassName()->shouldReturn($className);
    }

    function it_returns_id_definition()
    {
        $idDefinition = new Identity("id");
        $this->beConstructedWith(new ClassName(EntityFake::getClassName()), $idDefinition);
        $this->getIdDefinition()->shouldReturn($idDefinition);
    }

    function it_fits_for_entity_that_is_an_instance_of_class()
    {
        $this->beConstructedWith(new ClassName(EntityFake::getClassName()), new Identity("id"));
        $this->fitsFor(new EntityFake())->shouldReturn(true);
    }

    function it_can_have_new_command_handler(NewCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName(EntityFake::getClassName()), new Identity("id"));
        $this->setNewCommandHandler($commandHandler);
        $this->hasNewCommandHandler()->shouldReturn(true);
    }

    function it_can_have_edit_command_handler(EditCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName(EntityFake::getClassName()), new Identity("id"));
        $this->setEditCommandHandler($commandHandler);
        $this->hasEditCommandHandler()->shouldReturn(true);
    }

    function it_can_have_remove_command_handler(RemoveCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName(EntityFake::getClassName()), new Identity("id"));
        $this->setRemoveCommandHandler($commandHandler);
        $this->hasRemoveCommandHandler()->shouldReturn(true);
    }
}
