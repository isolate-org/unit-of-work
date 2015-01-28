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
        $className = new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake");
        $this->beConstructedWith($className, new Identity("id"));

        $this->shouldThrow(new InvalidArgumentException("Id definition property path can't be between observer properties."))
            ->during("addToObserved", [new Property("id")]);
    }

    function it_force_observed_properties_to_be_unique()
    {
        $className = new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake");
        $this->beConstructedWith($className, new Identity("id"));

        $this->setObserved([new \Isolate\UnitOfWork\Entity\Definition\Property("firstName"), new Property("firstName")]);

        $this->getObservedProperties()->shouldHaveCount(1);

        $this->setObserved([]);
        $this->addToObserved(new Property("firstName"));
        $this->addToObserved(new \Isolate\UnitOfWork\Entity\Definition\Property("firstName"));
        $this->getObservedProperties()->shouldHaveCount(1);
    }

    function it_returns_class_name()
    {
        $className = new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake");
        $this->beConstructedWith($className, new \Isolate\UnitOfWork\Entity\Definition\Identity("id"));
        $this->getClassName()->shouldReturn($className);
    }

    function it_returns_id_definition()
    {
        $idDefinition = new \Isolate\UnitOfWork\Entity\Definition\Identity("id");
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), $idDefinition);
        $this->getIdDefinition()->shouldReturn($idDefinition);
    }

    function it_fits_for_entity_that_is_an_instance_of_class()
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new \Isolate\UnitOfWork\Entity\Definition\Identity("id"));
        $this->fitsFor(new EntityFake())->shouldReturn(true);
    }

    function it_can_have_new_command_handler(NewCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new Identity("id"));
        $this->addNewCommandHandler($commandHandler);
        $this->hasNewCommandHandler()->shouldReturn(true);
    }

    function it_can_have_edit_command_handler(EditCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new \Isolate\UnitOfWork\Entity\Definition\Identity("id"));
        $this->addEditCommandHandler($commandHandler);
        $this->hasEditCommandHandler()->shouldReturn(true);
    }

    function it_can_have_remove_command_handler(RemoveCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new \Isolate\UnitOfWork\Entity\Definition\Identity("id"));
        $this->addRemoveCommandHandler($commandHandler);
        $this->hasRemoveCommandHandler()->shouldReturn(true);
    }
}
