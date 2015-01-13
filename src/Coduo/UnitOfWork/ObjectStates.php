<?php

namespace Coduo\UnitOfWork;

class ObjectStates
{
    /**
     * New created object that was never persisted and does not have identity
     */
    const NEW_OBJECT = 0;

    /**
     * Persisted object that already saved to data source and have identity
     */
    const PERSISTED_OBJECT = 1;

    /**
     * Persisted object that was saved to data source in past but it was
     * modified after registration in unit of work.
     */
    const EDITED_OBJECT = 2;

    const REMOVED_OBJECT = 3;
}
