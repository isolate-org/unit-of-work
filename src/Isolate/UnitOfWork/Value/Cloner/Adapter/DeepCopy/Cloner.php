<?php

namespace Isolate\UnitOfWork\Value\Cloner\Adapter\DeepCopy;

use DeepCopy\DeepCopy;
use Isolate\UnitOfWork\Value\Cloner as BaseCloner;

class Cloner implements BaseCloner
{
    /**
     * @var DeepCopy
     */
    private $cloner;

    public function __construct()
    {
        $this->cloner = new DeepCopy();
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function cloneValue($value)
    {
        return $this->cloner->copy($value);
    }
}
