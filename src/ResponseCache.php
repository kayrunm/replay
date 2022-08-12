<?php

namespace Kayrunm\Replay;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kayrunm\Replay\Cache\Cache as CacheContract;
use Symfony\Component\HttpFoundation\Response;

class ResponseCache implements CacheContract
{
    private Lock $lock;

    public function get(Request $request): ?ReplayResponse
    {
        if ($data = Cache::get($this->getKey($request))) {
            return ReplayResponse::fromArray($data);
        }

        return null;
    }

    public function lock(Request $request): bool
    {
        return $this->lock = Cache::lock($this->getKey($request))->get();
    }

    public function release(): void
    {
        $this->lock->release();
    }

    public function put(Request $request, Response $response): void
    {
        Cache::put(
            $this->getKey($request),
            ReplayResponse::fromResponse($response)->toArray(),
            Carbon::now()->add(config('replay.expires_in'))
        );
    }

    private function getKey(Request $request): string
    {
        $key = is_array($key = $request->header(config('replay.header')))
            ? $key[0]
            : $key;

        return md5("$key:{$request->path()}");
    }
}
