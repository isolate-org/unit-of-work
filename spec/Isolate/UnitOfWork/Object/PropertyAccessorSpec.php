<?php

namespace spec\Isolate\UnitOfWork\Object;

use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Tests\Double\ProtectedEntity;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertyAccessorSpec extends ObjectBehavior
{
    function it_throw_exception_on_attempt_to_access_property_not_on_object()
    {
        $this->shouldThrow(
            new InvalidArgumentException("PropertyAccessor require object to access property, \"array\" passed.")
        )->during("getValue", [[], "property"]);
    }

    function it_throw_exception_when_accessing_not_existing_property()
    {
        $object = new ProtectedEntity();
        $this->shouldThrow(
            new NotExistingPropertyException("Property \"notExistingPropertyName\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\ProtectedEntity\" class.")
        )->during("getValue", [$object, "notExistingPropertyName"]);
    }

    function it_can_read_value_from_private_property()
    {
        $object = new ProtectedEntity(124);
        $this->getValue($object, "privateProperty")->shouldReturn(124);
    }

    function it_can_read_value_from_protected_property()
    {
        $object = new ProtectedEntity(124, 256);
        $this->getValue($object, "protectedProperty")->shouldReturn(256);
    }

    function it_can_read_value_from_public_property()
    {
        $object = new ProtectedEntity();
        $object->publicProperty = 64;
        $this->getValue($object, "publicProperty")->shouldReturn(64);
    }


    function it_throw_exception_on_attempt_to_set_property_not_on_object()
    {
        $this->shouldThrow(
            new InvalidArgumentException("PropertyAccessor require object to access property, \"array\" passed.")
        )->during("setValue", [[], "property", 64]);
    }

    function it_throw_exception_when_setting_not_existing_property()
    {
        $object = new ProtectedEntity();
        $this->shouldThrow(
            new NotExistingPropertyException("Property \"notExistingPropertyName\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\ProtectedEntity\" class.")
        )->during("setValue", [$object, "notExistingPropertyName", 64]);
    }

    function it_can_set_value_from_private_property()
    {
        $object = new ProtectedEntity();
        $this->setValue($object, "privateProperty", 124);
        $this->getValue($object, "privateProperty")->shouldReturn(124);
    }

    function it_can_set_value_from_protected_property()
    {
        $object = new ProtectedEntity();
        $this->setValue($object, "protectedProperty", 256);
        $this->getValue($object, "protectedProperty")->shouldReturn(256);
    }

    function it_can_set_value_from_public_property()
    {
        $object = new ProtectedEntity();
        $this->setValue($object, "publicProperty", 64);
        $this->getValue($object, "publicProperty")->shouldReturn(64);
    }

}
