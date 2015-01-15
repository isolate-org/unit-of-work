<?php

namespace spec\Coduo\UnitOfWork;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeSpec extends ObjectBehavior
{
    function it_have_new_value_old_value_and_property_path()
    {
        $this->beConstructedWith("Foo", "Bar", "value");
        $this->getOriginValue()->shouldReturn("Foo");
        $this->getNewValue()->shouldReturn("Bar");
        $this->getPropertyPath()->shouldReturn("value");
    }
}
