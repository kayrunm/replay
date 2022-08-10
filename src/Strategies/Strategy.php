<?php

namespace Kayrunm\Replay\Strategies;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface Strategy
{
    public function isIdempotent(Request $request): bool;

    public function shouldCache(Response $response): bool;
}
