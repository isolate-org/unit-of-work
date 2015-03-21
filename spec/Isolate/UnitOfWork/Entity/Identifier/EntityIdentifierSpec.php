<?php

namespace spec\Isolate\UnitOfWork\Entity\Identifier;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EntityIdentifierSpec extends ObjectBehavior
{
    function let(Definition\Repository $definitions)
    {
        $definitions->hasDefinition(Argument::type('stdClass'))->willReturn(false);
        $this->beConstructedWith($definitions);
    }

    function it_is_identifier()
    {
        $this->shouldImplement("Isolate\\UnitOfWork\\Entity\\Identifier");
    }

    function it_throw_exception_during_verification_of_non_defined_entity()
    {
        $this->shouldThrow(new RuntimeException("Class \"stdClass\" does not have definition."))
            ->during("isPersisted", [new \stdClass()]);
    }

    function it_throw_exception_during_identification_of_non_defined_entity()
    {
        $this->shouldThrow(new RuntimeException("Class \"stdClass\" does not have definition."))
            ->during("getIdentity", [new \stdClass()]);
    }

    function it_use_entity_definition_to_tells_if_entity_was_persisted(Definition\Repository $definitions, Definition\IdentificationStrategy $identificationStrategy)
    {
        $entity = new EntityFake(1);
        $identificationStrategy->isIdentified($entity)->willReturn(true);
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"), $identificationStrategy->getWrappedObject())
        );

        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_throws_exception_during_persist_check_when_property_does_not_exists(Definition\Repository $definitions)
    {
        $entity = new EntityFake();
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("not_exists"))
        );

        $this->shouldThrow(
            new NotExistingPropertyException("Property \"not_exists\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" class.")
        )->during("isPersisted", [$entity]);
    }

    function it_gets_identity_from_entity(Definition\Repository $definitions, Definition\IdentificationStrategy $identificationStrategy)
    {
        $entity = new EntityFake(1);
        $identificationStrategy->getIdentity($entity)->willReturn(1);
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"), $identificationStrategy->getWrappedObject())
        );

        $this->getIdentity($entity)->shouldReturn(1);
    }
}
