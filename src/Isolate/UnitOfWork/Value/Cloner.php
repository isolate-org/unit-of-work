<?php

namespace Isolate\UnitOfWork\Value;

interface Cloner
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function cloneValue($value);
}
