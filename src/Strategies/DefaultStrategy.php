<?php

namespace Kayrunm\Replay\Strategies;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultStrategy implements Strategy
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
