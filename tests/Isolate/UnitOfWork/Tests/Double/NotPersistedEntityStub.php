<?php

namespace Isolate\UnitOfWork\Tests\Double;

class NotPersistedEntityStub
{
    protected $name;

    public function getId()
    {
        return null;
    }

    public static function getClassName()
    {
        return __CLASS__;
    }
}
