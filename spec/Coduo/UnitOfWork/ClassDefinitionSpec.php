<?php

namespace spec\Coduo\UnitOfWork;

use Coduo\UnitOfWork\Command\NewCommandHandler;
use Coduo\UnitOfWork\Exception\InvalidArgumentException;
use Coduo\UnitOfWork\IdDefinition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ClassDefinitionSpec extends ObjectBehavior
{
    function it_throw_exception_if_created_with_non_string_class_name()
    {
        $this->shouldThrow(new InvalidArgumentException("Class name must be a valid string."))
            ->during("__construct", [null, new IdDefinition("id")]);
    }

    function it_throw_exception_if_class_not_exists()
    {
        $this->shouldThrow(new InvalidArgumentException("Class \"Coduo\" does not exists."))
            ->during("__construct", ["Coduo", new IdDefinition("id")]);
    }

    function it_returns_class_name()
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("id"));
        $this->getClassName()->shouldReturn("\\DateTime");
    }

    function it_returns_id_definition()
    {
        $idDefinition = new IdDefinition("id");
        $this->beConstructedWith("\\DateTime", $idDefinition);
        $this->getIdDefinition()->shouldReturn($idDefinition);
    }

    function it_fits_for_object_that_is_an_instance_of_class()
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("time"));
        $this->fitsFor(new \DateTime())->shouldReturn(true);
    }

    function it_can_have_new_command_handler(NewCommandHandler $commandHandler)
    {
        $this->beConstructedWith("\\DateTime", new IdDefinition("time"));
        $this->addNewCommandHandler($commandHandler);
        $this->hasNewCommandHandler()->shouldReturn(true);
    }
}
