<?php

namespace Kayrunm\Replay\Exceptions;

use Exception;

class MatchingRequestStillExecuting extends Exception
{
    public function __construct()
    {
        parent::__construct('Another request with the same idempotency key is currently executing.');
    }
}
