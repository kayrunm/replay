<?php

namespace Kayrunm\Replay;

use Illuminate\Http\Request;
use Kayrunm\Replay\Contracts\IdempotencyStrategy;
use Symfony\Component\HttpFoundation\Response;

class DefaultIdempotencyStrategy implements IdempotencyStrategy
{
    public function isIdempotent(Request $request): bool
    {
        return $request->isMethod('POST')
            && $request->hasHeader(config('replay.header'));
    }

    public function shouldCache(Response $response): bool
    {
        return $response->isSuccessful();
    }
}
