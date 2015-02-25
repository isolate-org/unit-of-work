<?php

namespace spec\Isolate\UnitOfWork\Entity\Identifier;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Exception\InvalidPropertyPathException;
use Isolate\UnitOfWork\Exception\NotExistingPropertyException;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertyValueIdentifierSpec extends ObjectBehavior
{
    function let(Definition\Repository $definitions)
    {
        $definitions->hasDefinition(Argument::type('DateTime'))->willReturn(false);
        $this->beConstructedWith($definitions);
    }

    function it_is_identifier()
    {
        $this->shouldImplement("Isolate\\UnitOfWork\\Entity\\Identifier");
    }

    function it_throw_exception_during_verification_of_non_defined_entity()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("isPersisted", [new \DateTime()]);
    }

    function it_throw_exception_during_identification_of_non_defined_entity()
    {
        $this->shouldThrow(new RuntimeException("Class \"DateTime\" does not have definition."))
            ->during("getIdentity", [new \DateTime()]);
    }

    function it_tells_that_entity_is_persisted_when_it_has_not_empty_identity(Definition\Repository $definitions)
    {
        $entity = new EntityFake(1);
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))
        );

        $this->isPersisted($entity)->shouldReturn(true);
    }

    function it_tells_that_entity_is_not_persisted_when_it_has_empty_identity(Definition\Repository $definitions)
    {
        $entity = new EntityFake();
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))
        );

        $this->isPersisted($entity)->shouldReturn(false);
    }

    function it_tells_that_entity_is_persisted_when_it_has_identity_equal_to_zero(Definition\Repository $definitions)
    {
        $entity = new EntityFake(0);
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))
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

    function it_gets_identity_from_entity(Definition\Repository $definitions)
    {
        $entity = new EntityFake(1);
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))
        );

        $this->getIdentity($entity)->shouldReturn(1);
    }

    function it_throws_exception_on_get_identity_when_identity_path_does_not_exists(Definition\Repository $definitions)
    {
        $entity = new EntityFake(1);
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("not_exists"))
        );

        $this->shouldThrow(
            new NotExistingPropertyException("Property \"not_exists\" does not exists in \"Isolate\\UnitOfWork\\Tests\\Double\\EntityFake\" class.")
        )->during("getIdentity", [$entity]);
    }

    function it_throws_exception_when_entity_is_not_persisted_but_you_want_to_get_identity(Definition\Repository $definitions)
    {
        $entity = new EntityFake();
        $definitions->hasDefinition($entity)->willReturn(true);
        $definitions->getDefinition($entity)->willReturn(
            new Definition(new ClassName(EntityFake::getClassName()),new Definition\Identity("id"))
        );

        $this->shouldThrow(
            new RuntimeException(sprintf("Entity \"%s\" was not persisted yet.", get_class($entity)))
        )->during("getIdentity", [$entity]);
    }
}
