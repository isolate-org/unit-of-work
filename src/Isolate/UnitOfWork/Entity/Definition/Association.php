<?php

namespace Isolate\UnitOfWork\Entity\Definition;

use Isolate\UnitOfWork\Entity\ClassName;
use Isolate\UnitOfWork\Exception\InvalidArgumentException;

final class Association
{
    /**
     * Entity to other entity
     */
    const TO_SINGLE_ENTITY = 0;

    /**
     * Entity to many entities
     */
    const TO_MANY_ENTITIES = 1;

    /**
     * @var ClassName
     */
    private $target;

    /**
     * @var int
     */
    private $type;

    /**
     * @param ClassName $target
     * @param $type
     * @throws InvalidArgumentException
     */
    public function __construct(ClassName $target, $type)
    {
        $this->validateAssociationType($type);

        $this->target = $target;
        $this->type = $type;
    }

    /**
     * @return ClassName
     */
    public function getTargetClassName()
    {
        return $this->target;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @throws InvalidArgumentException
     */
    private function validateAssociationType($type)
    {
        if ($type != self::TO_SINGLE_ENTITY && $type != self::TO_MANY_ENTITIES) {
            throw new InvalidArgumentException("Unknown association type.");
        }
    }
}
