<?php

namespace spec\Isolate\UnitOfWork\Entity\Value\Change;

use Isolate\UnitOfWork\Entity\Definition\Property;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ScalarChangeSpec extends ObjectBehavior
{
    function it_is_a_change()
    {
        $this->beConstructedWith(new Property('property'), "Foo", "Bar");
    }

    function it_have_new_value_old_value_and_property_name()
    {
        $property = new Property('property');
        $this->beConstructedWith($property, "Foo", "Bar");
        $this->getOriginValue()->shouldReturn("Foo");
        $this->getNewValue()->shouldReturn("Bar");
        $this->getProperty()->shouldReturn($property);
    }
}
