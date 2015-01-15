<?php

namespace spec\Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\EditCommandHandler;
use Coduo\UnitOfWork\Command\NewCommandHandler;
use Coduo\UnitOfWork\Command\RemoveCommandHandler;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\IdDefinition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClassDefinitionSpec extends ObjectBehavior
{
    function it_throws_exception_if_created_with_non_string_class_name()
    {
        $this->shouldThrow(new InvalidArgumentException("Class name must be a valid string."))
            ->during("__construct", [null, new IdDefinition("id"), ["name"]]);
    }

    function it_throws_exception_if_class_not_exists()
    {
        $this->shouldThrow(new InvalidArgumentException("Class \"Coduo\" does not exists."))
            ->during("__construct", ["Coduo", new IdDefinition("id"), ["name"]]);
    }

    function it_throws_exception_when_observed_property_paths_are_empty()
    {
        $this->shouldThrow(new InvalidArgumentException("You need to observe at least one property."))
            ->during("__construct", ["\\DateTime", new IdDefinition("id"), []]);
    }

    function it_throws_exception_when_class_id_is_between_observed_property_paths()
    {
        $this->shouldThrow(new InvalidArgumentException("Id definition property path can't be between observer property paths."))
            ->during("__construct", ["\\DateTime", new IdDefinition("id"), ["id"]]);
    }

    function it_returns_class_name()
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("id"), ["time"]);
        $this->getClassName()->shouldReturn("\\DateTime");
    }

    function it_returns_id_definition()
    {
        $idDefinition = new IdDefinition("id");
        $this->beConstructedWith("\\DateTime", $idDefinition, ["time"]);
        $this->getIdDefinition()->shouldReturn($idDefinition);
    }

    function it_fits_for_object_that_is_an_instance_of_class()
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("id"), ["time"]);
        $this->fitsFor(new \DateTime())->shouldReturn(true);
    }

    function it_can_have_new_command_handler(NewCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("id"), ["time"]);
        $this->addNewCommandHandler($commandHandler);
        $this->hasNewCommandHandler()->shouldReturn(true);
    }

    function it_can_have_edit_command_handler(EditCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("id"), ["time"]);
        $this->addEditCommandHandler($commandHandler);
        $this->hasEditCommandHandler()->shouldReturn(true);
    }

    function it_can_have_remove_command_handler(RemoveCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("id"), ["time"]);
        $this->addRemoveCommandHandler($commandHandler);
        $this->hasRemoveCommandHandler()->shouldReturn(true);
    }
}
