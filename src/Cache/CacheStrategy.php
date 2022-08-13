<?php

namespace Kayrunm\Replay\Cache;

use Illuminate\Http\Request;
use Kayrunm\Replay\ReplayResponse;
use Symfony\Component\HttpFoundation\Response;

interface CacheStrategy
{
    public function get(Request $request): ?ReplayResponse;

    public function put(Request $request, Response $response): void;

    public function lock(Request $request): bool;

    public function release(): void;
}
