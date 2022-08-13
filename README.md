# 📽 Replay
A simple package for handling idempotent requests in Laravel.

## Installation

Require Replay using Composer:
```bash
composer require kayrunm/replay
```

## Configuration

Replay works out-of-the-box, but you can configure it further to fit your needs. To get started,
publish the config file with the following command:

```bash
php artisan vendor:publish --tag="replay"
```

This package uses the strategy pattern for both determining which requests should be idempotent
and for storing their responses in the cache. You can view the default strategies for these
below:

* [DefaultCacheStrategy](src/DefaultCacheStrategy.php)
* [DefaultIdempotencyStrategy](src/DefaultIdempotencyStrategy.php)

If you decide to implement your own strategies for either of the above, simply update the
config file with the relevant strategies, for example:

```php
'strategies' => [

    'caching' => \Acme\CustomCacheStrategy::class,
    
    'idempotency' => \Acme\CustomIdempotencyStrategy::class,
    
],
```

You can also customise the header used for the idempotency key (which is used in 
`DefaultIdempotencyStrategy`, which you could change to use a query parameter, if you wished),
as well as how long an idempotent request should stay in the cache (which is used in
`DefaultCacheStrategy`)

## Licence

Replay is an open-sourced software licensed under the [MIT](LICENSE) license.
