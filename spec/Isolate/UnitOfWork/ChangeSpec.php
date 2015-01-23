<?php

namespace spec\Isolate\UnitOfWork;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeSpec extends ObjectBehavior
{
    function it_have_new_value_old_value_and_property_name()
    {
        $this->beConstructedWith("Foo", "Bar", "value");
        $this->getOriginValue()->shouldReturn("Foo");
        $this->getNewValue()->shouldReturn("Bar");
        $this->getPropertyName()->shouldReturn("value");
    }
}
