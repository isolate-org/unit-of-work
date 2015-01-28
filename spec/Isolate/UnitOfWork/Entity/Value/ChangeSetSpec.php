<?php

namespace spec\Isolate\UnitOfWork\Entity\Value;

use Isolate\UnitOfWork\Entity\Value\Change;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Exception\RuntimeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeSetSpec extends ObjectBehavior
{
    function it_is_an_instance_of_array_object()
    {
        $this->shouldBeAnInstanceOf('ArrayObject');
    }

    function it_has_information_about_changes_for_specific_property_name()
    {
        $change = new Change(new Property("firstName"), "Michal", "Norbert");
        $this->beConstructedWith([$change]);

        $this->hasChangeFor("firstName")->shouldReturn(true);
        $this->getChangeFor("firstName")->shouldReturn($change);
    }

    function it_throws_exception_when_there_are_no_changes_for_property()
    {
        $this->beConstructedWith([]);

        $this->shouldThrow(new RuntimeException("There are no changes for \"firstName\" property."))
            ->during('getChangeFor', ["firstName"]);
    }
}
