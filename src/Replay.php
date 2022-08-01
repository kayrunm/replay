<?php

namespace Kayrunm\Replay;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kayrunm\Replay\Strategies\Strategy;

class Replay
{
    private ResponseCache $cache;
    private Strategy $strategy;

    public function __construct(
        ResponseCache $cache,
        Strategy $strategy
    ) {
        $this->cache = $cache;
        $this->strategy = $strategy;
    }

    /** @return mixed */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->strategy->isIdempotent($request)) {
            return $next($request);
        }

        if ($response = $this->cache->get($request)) {
            return $response->toResponse();
        }

        /** @var Response $response */
        $response = $next($request);

        if ($this->strategy->shouldCache($response)) {
            $this->cache->put($request, $response);
        }

        return $response;
    }
}
