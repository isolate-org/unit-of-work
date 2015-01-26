<?php

namespace Isolate\UnitOfWork;

final class Events
{
    const PRE_COMMIT = 'isolate.unit_of_work.pre_commit';

    const POST_COMMIT = 'isolate.unit_of_work.post_commit';

    const PRE_REGISTER_ENTITY = 'isolate.unit_of_work.pre_register_entity';

    const PRE_GET_ENTITY_STATE = 'isolate.unit_of_work.pre_get_entity_state';

    const PRE_REMOVE_ENTITY = 'isolate.unit_of_work.pre_remove_entity';
}
