<?php

return [

    'header' => 'X-Idempotency-Key',

    'strategies' => [
        'caching' => \Kayrunm\Replay\Cache\DefaultCacheStrategy::class,
        'idempotency' => \Kayrunm\Replay\Idempotency\DefaultIdempotencyStrategy::class,
    ],

    'expires_in' => \Carbon\CarbonInterval::createFromDateString('1 day'),
];
