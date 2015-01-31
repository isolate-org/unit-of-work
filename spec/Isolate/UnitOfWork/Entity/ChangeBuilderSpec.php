<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Property\ValueComparer;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeBuilderSpec extends ObjectBehavior
{
    function it_build_change_for_different_objects()
    {
        $firstObject = new EntityFake(1, "Norbert");
        $secondObject = clone($firstObject);
        $secondObject->changeFirstName("Michal");

        $changeSet = $this->buildChanges($this->createEntityDefinition(), $firstObject, $secondObject);
        $changeSet->hasChangeFor('firstName')->shouldReturn(true);
        $changeSet->getChangeFor('firstName')->getOriginValue()->shouldReturn("Norbert");
        $changeSet->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\ChangeSet");
    }

    /**
     * @return Definition
     */
    private function createEntityDefinition()
    {
        $definition = new Definition(
            new ClassName(EntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $definition->addToObserved(new Definition\Property("firstName"));

        return $definition;
    }
}

