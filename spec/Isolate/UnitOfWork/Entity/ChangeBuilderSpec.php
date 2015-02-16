<?php

namespace spec\Isolate\UnitOfWork\Entity;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\InformationPoint;
use Isolate\UnitOfWork\Exception\RuntimeException;
use Isolate\UnitOfWork\Tests\Double\AssociatedEntityFake;
use Isolate\UnitOfWork\Tests\Double\EntityFake;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChangeBuilderSpec extends ObjectBehavior
{
    function let(InformationPoint $informationPoint)
    {
        $informationPoint->getDefinition(Argument::type(EntityFake::getClassName()))
            ->willReturn($this->createEntityDefinition());
        $informationPoint->getDefinition(Argument::type(AssociatedEntityFake::getClassName()))
            ->willReturn($this->createAssociatedEntityDefinition());

        $informationPoint->isPersisted(Argument::any())->will(function ($args) {
            return !is_null($args[0]->getId());
        });

        $informationPoint->getIdentity(Argument::any())->will(function ($args) {
            return $args[0]->getId();
        });

        $this->beConstructedWith($informationPoint);
    }

    function it_build_change_for_different_objects()
    {
        $sourceObject = new EntityFake(1, "Norbert");
        $editedObject = clone($sourceObject);
        $editedObject->changeFirstName("Michal");

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor('firstName')->shouldReturn(true);
        $changeSet->getChangeFor('firstName')->getOriginValue()->shouldReturn("Norbert");
        $changeSet->getChangeFor('firstName')->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\ScalarChange");
        $changeSet->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\ChangeSet");
    }

    function it_throws_exception_when_new_entity_in_associated_property_does_not_match_target_class()
    {
        $sourceObject = new AssociatedEntityFake(1);
        $editedObject = clone($sourceObject);
        $editedObject->setParent(new EntityFake());

        $this->shouldThrow(new RuntimeException(
                sprintf("Property \"parent\" expects instanceof \"%s\" as a value.", AssociatedEntityFake::getClassName())
            ))->during("buildChanges", [$sourceObject, $editedObject]);
    }

    function it_build_single_association_change_for_new_entity()
    {
        $parent = new AssociatedEntityFake(100);

        $sourceObject = new AssociatedEntityFake(1);
        $editedObject = clone($sourceObject);
        $editedObject->setParent($parent);

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("parent")->shouldReturn(true);
        $changeSet->getChangeFor("parent")->getNewValue()->shouldReturn($parent);
        $changeSet->getChangeFor("parent")->isPersisted()->shouldReturn(true);
        $changeSet->getChangeFor("parent")->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\NewEntity");
    }

    function it_build_change_when_single_associated_entity_was_removed()
    {
        $parent = new AssociatedEntityFake(100);

        $sourceObject = new AssociatedEntityFake(1, null, $parent);
        $editedObject = clone($sourceObject);
        $editedObject->removeParent();

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("parent")->shouldReturn(true);
        $changeSet->getChangeFor("parent")->getOriginValue()->shouldReturn($parent);
        $changeSet->getChangeFor("parent")->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\RemovedEntity");
    }

    function it_build_single_association_change_for_edited_entity()
    {
        $sourceObject = new AssociatedEntityFake(1, null, new AssociatedEntityFake(100, "Norbert"));
        $editedObject = new AssociatedEntityFake(1, null, new AssociatedEntityFake(100, "Norbert"));
        $editedObject->getParent()->setName("Dawid");

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("parent")->shouldReturn(true);
        $changeSet->getChangeFor("parent")->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\EditedEntity");
        $changeSet->getChangeFor("parent")->getChangeSet()->getChangeFor("name")->shouldBeAnInstanceOf(
            "Isolate\\UnitOfWork\\Entity\\Value\\Change\\ScalarChange"
        );
        $changeSet->getChangeFor("parent")->getChangeSet()->getChangeFor("name")->getOriginValue()->shouldReturn("Norbert");
        $changeSet->getChangeFor("parent")->getChangeSet()->getChangeFor("name")->getNewValue()->shouldReturn("Dawid");
    }

    function it_throws_exception_when_new_associated_collection_is_not_valid_array()
    {
        $sourceObject = new AssociatedEntityFake(1, null, null);
        $editedObject = new AssociatedEntityFake(1, null, null, "test");

        $this->shouldThrow(
                new RuntimeException("Property \"children\" is marked as associated with many entities and require new value to be traversable collection.")
            )->during("buildChanges", [$sourceObject, $editedObject]);
    }

    function it_build_change_for_many_entities_association_that_knows_which_entities_were_added()
    {
        $sourceObject = new AssociatedEntityFake(1, null, null, []);
        $editedObject = new AssociatedEntityFake(1, null, null, [new AssociatedEntityFake()]);

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("children")->shouldReturn(true);
        $change = $changeSet->getChangeFor("children");
        $change->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\AssociatedCollection");
        $change->getChangeForNewEntities()->shouldHaveCount(1);
        $change->getChangesForRemovedEntities()->shouldHaveCount(0);
        $change->getChangesForEditedEntities()->shouldHaveCount(0);
    }

    function it_build_change_for_many_entities_association_that_knows_which_entities_were_added_even_persisted()
    {
        $sourceObject = new AssociatedEntityFake(1, null, null, []);
        $editedObject = new AssociatedEntityFake(1, null, null, [new AssociatedEntityFake(100)]);

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("children")->shouldReturn(true);
        $change = $changeSet->getChangeFor("children");
        $change->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\AssociatedCollection");
        $change->getChangeForNewEntities()->shouldHaveCount(1);
        $change->getChangesForRemovedEntities()->shouldHaveCount(0);
        $change->getChangesForEditedEntities()->shouldHaveCount(0);
    }

    function it_build_change_for_many_entities_association_that_knows_which_entities_were_removed()
    {
        $sourceObject = new AssociatedEntityFake(1, null, null, [new AssociatedEntityFake(100)]);
        $editedObject = new AssociatedEntityFake(1, null, null, []);

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("children")->shouldReturn(true);
        $change = $changeSet->getChangeFor("children");
        $change->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\AssociatedCollection");
        $change->getChangeForNewEntities()->shouldHaveCount(0);
        $change->getChangesForRemovedEntities()->shouldHaveCount(1);
        $change->getChangesForEditedEntities()->shouldHaveCount(0);
    }

    function it_build_change_for_many_entities_association_that_knows_which_entities_were_edited()
    {
        $sourceObject = new AssociatedEntityFake(1, null, null, [new AssociatedEntityFake(100)]);
        $editedObject = new AssociatedEntityFake(1, null, null, [new AssociatedEntityFake(100, "Norbert")]);

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("children")->shouldReturn(true);
        $change = $changeSet->getChangeFor("children");
        $change->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\AssociatedCollection");
        $change->getChangeForNewEntities()->shouldHaveCount(0);
        $change->getChangesForRemovedEntities()->shouldHaveCount(0);
        $change->getChangesForEditedEntities()->shouldHaveCount(1);
    }

    function it_build_change_for_many_entities_association_that_has_new_edited_and_removed_elements()
    {
        $sourceObject = new AssociatedEntityFake(1, null, null, [
            new AssociatedEntityFake(100),
            new AssociatedEntityFake(101),
        ]);
        $editedObject = new AssociatedEntityFake(1, null, null, [
            new AssociatedEntityFake(101, "Norbert"),
            new AssociatedEntityFake()
        ]);

        $changeSet = $this->buildChanges($sourceObject, $editedObject);

        $changeSet->hasChangeFor("children")->shouldReturn(true);
        $change = $changeSet->getChangeFor("children");
        $change->shouldBeAnInstanceOf("Isolate\\UnitOfWork\\Entity\\Value\\Change\\AssociatedCollection");
        $change->getChangeForNewEntities()->shouldHaveCount(1);
        $change->getChangesForRemovedEntities()->shouldHaveCount(1);
        $change->getChangeForNewEntities()->shouldHaveCount(1);
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

    /**
     * @return Definition
     */
    private function createAssociatedEntityDefinition()
    {
        $definition = new Definition(
            new ClassName(AssociatedEntityFake::getClassName()),
            new Definition\Identity("id")
        );
        $parentAssociation = new Definition\Association(
            new ClassName(AssociatedEntityFake::getClassName()),
            Definition\Association::TO_SINGLE_ENTITY
        );

        $definition->setObserved([
            new Definition\Property("parent", $parentAssociation),
            new Definition\Property("children", $this->createChildrenAssociation()),
            new Definition\Property("name")
        ]);

        return $definition;
    }

    /**
     * @return Definition\Association
     */
    private function createChildrenAssociation()
    {
        return new Definition\Association(
            new ClassName(AssociatedEntityFake::getClassName()),
            Definition\Association::TO_MANY_ENTITIES
        );
    }
}

