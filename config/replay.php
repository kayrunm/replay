<?php

return [

    'header' => 'X-Idempotency-Key',

    'strategy' => \Kayrunm\Replay\Strategies\DefaultStrategy::class,

    'caching_strategy' => \Kayrunm\Replay\Cache\DefaultCacheStrategy::class,

    'expires_in' => \Carbon\CarbonInterval::createFromDateString('1 day'),
];
