<?php

namespace Kayrunm\Replay;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kayrunm\Replay\Cache\CacheStrategy;
use Kayrunm\Replay\Exceptions\MatchingRequestStillExecuting;
use Kayrunm\Replay\Strategies\Strategy;

class Replay
{
    private CacheStrategy $cache;
    private Strategy $strategy;

    public function __construct(
        CacheStrategy $cache,
        Strategy $strategy
    ) {
        $this->cache = $cache;
        $this->strategy = $strategy;
    }

    /**
     * @throws MatchingRequestStillExecuting
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->strategy->isIdempotent($request)) {
            return $next($request);
        }

        if ($response = $this->cache->get($request)) {
            return $response->toResponse();
        }

        if (! $this->cache->lock($request)) {
            throw new MatchingRequestStillExecuting();
        }

        /** @var Response $response */
        $response = $next($request);

        if ($this->strategy->shouldCache($response)) {
            $this->cache->put($request, $response);
        }

        $this->cache->release();

        return $response;
    }
}
