<?php

namespace Isolate\UnitOfWork\Tests\Double;

class PersistedEntityStub
{
    public function getId()
    {
        return 1;
    }

    public static function getClassName()
    {
        return __CLASS__;
    }
}
