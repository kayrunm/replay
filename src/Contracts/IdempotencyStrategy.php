<?php

namespace Kayrunm\Replay\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface IdempotencyStrategy
{
    public function isIdempotent(Request $request): bool;

    public function shouldCache(Response $response): bool;
}
