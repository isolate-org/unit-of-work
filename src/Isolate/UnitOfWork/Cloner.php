<?php

namespace Isolate\UnitOfWork;

interface Cloner
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function cloneValue($value);
}
