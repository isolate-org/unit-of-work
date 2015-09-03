<?php

namespace Isolate\UnitOfWork\Command;

/**
 * @api
 */
interface Command 
{
    /**
     * @return mixed
     * 
     * @api
     */
    public function getEntity();
}
