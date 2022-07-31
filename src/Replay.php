<?php

namespace Kayrunm\Replay;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kayrunm\Replay\Cache\Repository;

class Replay
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /** @return mixed */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasHeader('X-Idempotency-Key')) {
            return $next($request);
        }

        if ($response = $this->repository->get($request)) {
            return (new Response())
                ->setContent($response['content'])
                ->setStatusCode($response['status'])
                ->withHeaders(array_merge($response['headers'], [
                    'X-Is-Replay' => 'true',
                ]));
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->isSuccessful()) {
            $this->repository->put($request, $response);
        }

        return $response;
    }
}
