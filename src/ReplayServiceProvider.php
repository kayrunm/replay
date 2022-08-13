<?php

namespace Kayrunm\Replay;

use Illuminate\Support\ServiceProvider;
use Kayrunm\Replay\Contracts\CacheStrategy;
use Kayrunm\Replay\Contracts\IdempotencyStrategy;

class ReplayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/replay.php' => config_path('replay.php'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/replay.php', 'replay');

        $this->app->bind(CacheStrategy::class, config('replay.strategies.caching'));
        $this->app->bind(IdempotencyStrategy::class, config('replay.strategies.idempotency'));
    }
}
