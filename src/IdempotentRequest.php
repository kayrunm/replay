<?php

namespace Kayrunm\Replay;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class IdempotentRequest
{
    /** @return mixed */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasHeader('X-Idempotency-Key')) {
            return $next($request);
        }

        if (Cache::has($key = $this->getCacheKey($request))) {
            $response = Cache::get($key);

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
            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], Carbon::now()->addHours(24));
        }

        return $response;
    }

    private function getCacheKey(Request $request): string
    {
        $key = is_array($key = $request->header('X-Idempotency-Key'))
            ? $key[0]
            : $key;

        return md5("$key:{$request->path()}");
    }
}
