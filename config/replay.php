<?php

return [

    'header' => 'X-Idempotency-Key',

    'strategy' => \Kayrunm\Replay\Strategies\DefaultStrategy::class,

    'expires_in' => \Carbon\CarbonInterval::createFromDateString('1 day'),
];
