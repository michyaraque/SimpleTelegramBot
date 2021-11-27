<?php

namespace Enums;

abstract class StatusType extends \App\Libraries\Enum {

    const ACCEPTED = 'accepted';
    const BLOCKED = 'blocked';
    const REVISION = 'revision';
    const CANCELLED = 'cancelled';
}