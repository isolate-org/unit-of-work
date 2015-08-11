<?php

namespace Isolate\UnitOfWork\Entity\Property;

use Isolate\UnitOfWork\Entity\Definition\Property;

interface ValueComparer
{
    /**
     * @param Property $property
     * @param $firstObject
     * @param $secondObject
     * @return mixed
     */
    public function hasDifferentValue(Property $property, $firstObject, $secondObject);
}