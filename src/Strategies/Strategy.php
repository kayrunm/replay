<?php

namespace Kayrunm\Replay\Strategies;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface Strategy
{
    public function isIdempotent(Request $request): bool;

    public function shouldCache(Response $response): bool;
}
