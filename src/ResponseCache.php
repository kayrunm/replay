<?php

namespace Kayrunm\Replay;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use function config;

class ResponseCache
{
    public function get(Request $request): ?ReplayResponse
    {
        if ($data = Cache::get($this->getKey($request))) {
            return ReplayResponse::fromArray($data);
        }

        return null;
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
