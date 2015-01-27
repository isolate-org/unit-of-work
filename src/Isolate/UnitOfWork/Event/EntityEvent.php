<?php

namespace Isolate\UnitOfWork\Event;

trait EntityEvent
{
    protected $entity;

    /**
     * @param $newEntity
     */
    public function replaceEntity($newEntity)
    {
        $this->entity = $newEntity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
