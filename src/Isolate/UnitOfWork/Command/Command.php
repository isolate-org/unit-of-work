<?php

namespace Isolate\UnitOfWork\Command;

interface Command 
{
    /**
     * @return mixed
     */
    public function getEntity();
}
