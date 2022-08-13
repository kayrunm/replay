<?php

return [

    /**
     * Routes that use the Replay middleware will search for the idempotency key below. It's
     * a convention that custom headers start with the "X-" prefix, so we would recommend
     * that anything you choose to replace the header with follows the same convention.
     */
    'header' => 'X-Idempotency-Key',

    /**
     * This is how long idempotency keys and their requests are kept in the cache for.
     */
    'expires_in' => \Carbon\CarbonInterval::createFromDateString('1 day'),

    /**
     * You can replace the strategies used for deciding which requests are considered to
     * be idempotent and how requests are cached here. You can view the contracts for
     * the strategies under the `Kayrunm\Replay\Contracts directory`. Happy coding!
     */
    'strategies' => [

        'caching' => \Kayrunm\Replay\DefaultCacheStrategy::class,

        'idempotency' => \Kayrunm\Replay\DefaultIdempotencyStrategy::class,

    ],

];
