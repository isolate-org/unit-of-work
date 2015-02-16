<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComparerSpec extends ObjectBehavior
{
    function let(Definition\Repository $definitions)
    {
        $definitions->getDefinition(Argument::type(EntityFake::getClassName()))
            ->willReturn($this->createEntityDefinition());

        $this->beConstructedWith($definitions);
    }

    function it_returns_true_when_values_in_observed_properties_are_equal()
    {
        $firstObject = new EntityFake(1, "Norbert", "Orzechowicz");
        $secondObject = clone $firstObject;

        $this->areEqual($firstObject, $secondObject)->shouldReturn(true);
    }

    function it_returns_false_when_values_in_at_least_one_observed_property_is_different()
    {
        $firstObject = new EntityFake(1, "Norbert", "Orzechowicz");
        $secondObject = clone $firstObject;
        $secondObject->changeLastName('Sajdak');

        $this->areEqual($firstObject, $secondObject)->shouldReturn(false);
    }

    function it_ignores_not_observed_properties()
    {
        $firstObject = new EntityFake(1, "Norbert", "Orzechowicz");
        $secondObject = clone $firstObject;
        $secondObject->setItems(["Foo", "Bar"]);

        $this->areEqual($firstObject, $secondObject)->shouldReturn(true);
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
        $definition->addToObserved(new Definition\Property("lastName"));

        return $definition;
    }
}

