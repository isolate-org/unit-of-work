<?php

namespace Coduo\UnitOfWork;

final class Events
{
    const PRE_COMMIT = 'coduo.unit_of_work.pre_commit';

    const POST_COMMIT = 'coduo.unit_of_work.post_commit';
}
