<?php

namespace Kayrunm\Replay\Strategies;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DefaultStrategy implements Strategy
{
    public function isIdempotent(Request $request): bool
    {
        return $request->isMethod('POST')
            && $request->hasHeader('X-Idempotency-Key');
    }

    public function shouldCache(Response $response): bool
    {
        return $response->isSuccessful();
    }
}
