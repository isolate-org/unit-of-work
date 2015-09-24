<?php

namespace Isolate\UnitOfWork\Entity\Definition\Repository;

use Isolate\UnitOfWork\Entity\Definition;
use Isolate\UnitOfWork\Entity\Definition\Property;
use Isolate\UnitOfWork\Entity\Definition\Repository;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;
use Isolate\UnitOfWork\Exception\RuntimeException;

final class InMemory implements Repository
{
    /**
     * @var array|Definition[]
     */
    private $entityDefinitions;

    /**
     * @param array $entityDefinitions
     * @throws InvalidArgumentException
     */
    public function __construct($entityDefinitions = [])
    {
        $this->entityDefinitions = [];

        if (!is_array($entityDefinitions) && !$entityDefinitions instanceof \Traversable) {
            throw new InvalidArgumentException("Entity definition repository require array od traversable collection of entity definitions.");
        }

        foreach ($entityDefinitions as $definition) {
            if (!$definition instanceof Definition) {
                throw new InvalidArgumentException("Each element in collection needs to be an instance of Isolate\\UnitOfWork\\Entity\\Definition");
            }

            $this->entityDefinitions[(string) $definition->getClassName()] = $definition;
        }

        $this->validateAssociations();
    }

    /**
     * @param Definition $entityDefinition
     */
    public function addDefinition(Definition $entityDefinition)
    {
        $this->entityDefinitions[(string) $entityDefinition->getClassName()] = $entityDefinition;
    }

    /**
     * @param $entity
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasDefinition($entity)
    {
        if (!is_object($entity)) {
            throw new InvalidArgumentException("Entity definition repository require objects as arguments for methods.");
        }

        return array_key_exists(get_class($entity), $this->entityDefinitions);
    }

    /**
     * @param $entity
     * @return Definition
     * @throws RuntimeException
     */
    public function getDefinition($entity)
    {
        if (!$this->hasDefinition($entity)) {
            throw new RuntimeException(sprintf("Entity definition for \"%s\" does not exists.", get_class($entity)));
        }

        return $this->entityDefinitions[get_class($entity)];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateAssociations()
    {
        foreach ($this->entityDefinitions as $definition) {
            foreach ($definition->getObservedProperties() as $property) {
                $this->validateAssociation($definition, $property);
            }
        }
    }

    /**
     * @param Definition $definition
     * @param Property $property
     * @throws InvalidArgumentException
     */
    private function validateAssociation(Definition $definition, Property $property)
    {
        if ($property->isAssociated()) {
            $targetClass = (string) $property->getAssociation()->getTargetClassName();
            if (!array_key_exists($targetClass, $this->entityDefinitions)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "Entity class \"%s\" used in association of \"%s\" entity does not have definition.",
                        $targetClass,
                        (string)$definition->getClassName()
                    )
                );
            }
        }
    }
}
