<?php

namespace Isolate\UnitOfWork\Tests\Double;

class ProtectedEntity
{
    private $privateProperty;

    protected $protectedProperty;

    public $publicProperty;

    public function __construct($privateValue = null, $protectedValue  = null)
    {
        $this->privateProperty = $privateValue;
        $this->protectedProperty = $protectedValue;
    }
}
