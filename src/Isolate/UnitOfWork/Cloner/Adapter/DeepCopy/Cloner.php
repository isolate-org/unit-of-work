<?php

namespace Isolate\UnitOfWork\Cloner\Adapter\DeepCopy;

use DeepCopy\DeepCopy;
use Isolate\UnitOfWork\Cloner as BaseCloner;

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
