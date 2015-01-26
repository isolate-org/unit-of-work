<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Command\EditCommandHandler;
use Isolate\UnitOfWork\Command\NewCommandHandler;
use Isolate\UnitOfWork\Command\RemoveCommandHandler;
use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Entity\IdDefinition;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClassDefinitionSpec extends ObjectBehavior
{
    function it_throws_exception_when_class_id_is_between_observed_properties()
    {
        $this->shouldThrow(new InvalidArgumentException("Id definition property path can't be between observer properties."))
            ->during("__construct", [new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new IdDefinition("id"), ["id"]]);
    }

    function it_returns_class_name()
    {
        $className = new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake");
        $this->beConstructedWith($className, new IdDefinition("id"), ["firstName"]);
        $this->getClassName()->shouldReturn($className);
    }

    function it_returns_id_definition()
    {
        $idDefinition = new IdDefinition("id");
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), $idDefinition, ["firstName"]);
        $this->getIdDefinition()->shouldReturn($idDefinition);
    }

    function it_fits_for_entity_that_is_an_instance_of_class()
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new IdDefinition("id"), ["firstName"]);
        $this->fitsFor(new EntityFake())->shouldReturn(true);
    }

    function it_can_have_new_command_handler(NewCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new IdDefinition("id"), ["firstName"]);
        $this->addNewCommandHandler($commandHandler);
        $this->hasNewCommandHandler()->shouldReturn(true);
    }

    function it_can_have_edit_command_handler(EditCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new IdDefinition("id"), ["firstName"]);
        $this->addEditCommandHandler($commandHandler);
        $this->hasEditCommandHandler()->shouldReturn(true);
    }

    function it_can_have_remove_command_handler(RemoveCommandHandler $commandHandler)
    {
        $this->beConstructedWith(new ClassName("\\Isolate\\UnitOfWork\\Tests\\Double\\EntityFake"), new IdDefinition("id"), ["firstName"]);
        $this->addRemoveCommandHandler($commandHandler);
        $this->hasRemoveCommandHandler()->shouldReturn(true);
    }
}
