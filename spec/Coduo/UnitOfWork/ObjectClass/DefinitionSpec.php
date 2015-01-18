<?php

namespace spec\Coduo\UnitOfWork\ObjectClass;

use Coduo\UnitOfWork\Command\EditCommandHandler;
use Coduo\UnitOfWork\Command\NewCommandHandler;
use Coduo\UnitOfWork\Command\RemoveCommandHandler;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\ObjectClass\IdDefinition;
use Coduo\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefinitionSpec extends ObjectBehavior
{
    function it_throws_exception_if_created_with_non_string_class_name()
    {
        $this->shouldThrow(new InvalidArgumentException("Class name must be a valid string."))
            ->during("__construct", [null, new \Coduo\UnitOfWork\ObjectClass\IdDefinition("id"), ["name"]]);
    }

    function it_throws_exception_if_class_not_exists()
    {
        $this->shouldThrow(new InvalidArgumentException("Class \"Coduo\" does not exists."))
            ->during("__construct", ["Coduo", new IdDefinition("id"), ["name"]]);
    }

    function it_throws_exception_when_class_id_is_between_observed_properties()
    {
        $this->shouldThrow(new InvalidArgumentException("Id definition property path can't be between observer properties."))
            ->during("__construct", ["\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", new IdDefinition("id"), ["id"]]);
    }

    function it_returns_class_name()
    {
        $this->beConstructedWith("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", new \Coduo\UnitOfWork\ObjectClass\IdDefinition("id"), ["firstName"]);
        $this->getClassName()->shouldReturn("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake");
    }

    function it_returns_id_definition()
    {
        $idDefinition = new \Coduo\UnitOfWork\ObjectClass\IdDefinition("id");
        $this->beConstructedWith("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", $idDefinition, ["firstName"]);
        $this->getIdDefinition()->shouldReturn($idDefinition);
    }

    function it_fits_for_object_that_is_an_instance_of_class()
    {
        $this->beConstructedWith("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", new \Coduo\UnitOfWork\ObjectClass\IdDefinition("id"), ["firstName"]);
        $this->fitsFor(new EntityFake())->shouldReturn(true);
    }

    function it_can_have_new_command_handler(NewCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", new IdDefinition("id"), ["firstName"]);
        $this->addNewCommandHandler($commandHandler);
        $this->hasNewCommandHandler()->shouldReturn(true);
    }

    function it_can_have_edit_command_handler(EditCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", new IdDefinition("id"), ["firstName"]);
        $this->addEditCommandHandler($commandHandler);
        $this->hasEditCommandHandler()->shouldReturn(true);
    }

    function it_can_have_remove_command_handler(RemoveCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\Coduo\\UnitOfWork\\Tests\\Double\\EntityFake", new \Coduo\UnitOfWork\ObjectClass\IdDefinition("id"), ["firstName"]);
        $this->addRemoveCommandHandler($commandHandler);
        $this->hasRemoveCommandHandler()->shouldReturn(true);
    }
}
