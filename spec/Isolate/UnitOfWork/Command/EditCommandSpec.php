<?php

namespace spec\Isolate\UnitOfWork\Command;

use Isolate\UnitOfWork\Entity\Value\Change\ScalarChange;
use Isolate\UnitOfWork\Entity\Value\ChangeSet;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditCommandSpec extends ObjectBehavior
{
    function it_has_object_that_should_be_persisted()
    {
        $entity = new EntityFake(1, "Norbert", "Orzechowicz");
        $changes = new ChangeSet([new ScalarChange(new Property("firstName"), "Norbert", "Michal")]);
        $this->beConstructedWith($entity, $changes);
        $this->getEntity()->shouldReturn($entity);
        $this->getChanges()->shouldReturn($changes);
    }

    function it_throws_exception_when_created_for_not_a_object_value()
    {
        $this->shouldThrow(new InvalidArgumentException("Edit command require object \"string\" type passed."))
            ->during("__construct", ["this is string", new ChangeSet(), 1]);
    }
}
