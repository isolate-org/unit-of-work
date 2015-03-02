<?php

namespace spec\Isolate\UnitOfWork\Entity\Definition\IdentificationStrategy;

use Isolate\UnitOfWork\Entity\Definition\Identity;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertyValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new Identity("id"));
    }

    function it_is_identification_strategy()
    {
        $this->shouldHaveType('Isolate\UnitOfWork\Entity\Definition\IdentificationStrategy');
    }

    function it_identify_entities_with_not_empty_value_in_identity_property()
    {
        $this->isIdentified(new EntityFake(1))->shouldReturn(true);
    }

    function it_identify_entities_with_properties_that_contains_0_as_a_value()
    {
        $this->isIdentified(new EntityFake(0))->shouldReturn(true);
    }

    function it_gets_identity_from_entity()
    {
        $this->getIdentity(new EntityFake(1))->shouldReturn(1);
    }

    function it_throws_exception_when_entity_does_not_have_identity()
    {
        $this->shouldThrow(
            new RuntimeException("Can't get identity from not identified entity.")
        )->during("getIdentity", [new EntityFake()]);
    }
}
