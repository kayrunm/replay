<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kayrunm\Replay\Replay;
use Kayrunm\Replay\ReplayResponse;
use Kayrunm\Replay\ResponseCache;
use Kayrunm\Replay\Strategies\Strategy;
use Mockery\MockInterface;

class ReplayTest extends TestCase
{
    private Replay $middleware;
    private MockInterface $strategy;
    private MockInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategy = $this->mock(Strategy::class);
        $this->cache = $this->mock(ResponseCache::class);

        $this->middleware = $this->app->make(Replay::class);
    }

    public function test_requests_without_an_idempotency_key_run_as_normal(): void
    {
        $this->strategy
            ->shouldReceive('isIdempotent')
            ->andReturnFalse();

        $this->middleware->handle(new Request(), fn () => $this->assertTrue(true));
    }

    public function test_returns_a_response_from_the_cache(): void
    {
        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $this->strategy
            ->shouldReceive('isIdempotent')
            ->andReturnTrue();

        $this->cache
            ->shouldReceive('get')
            ->with($request)
            ->andReturn(ReplayResponse::fromArray([
                'content' => 'Hello world',
                'status' => 200,
                'headers' => ['X-Foo' => 'bar'],
            ]));

        $response = $this->middleware->handle($request, function () {});

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('Hello world', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('bar', $response->headers->get('X-Foo'));
        $this->assertSame('true', $response->headers->get('X-Is-Replay'));
    }

    public function test_it_stores_a_response_in_the_cache_based_on_the_strategy(): void
    {
        Carbon::setTestNow('2022-07-31 09:30:00');

        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $response = (new Response())
            ->setContent('Hello world')
            ->setStatusCode(200);

        $this->strategy
            ->shouldReceive('isIdempotent')
            ->andReturnTrue();

        $this->strategy
            ->shouldReceive('shouldCache')
            ->andReturnTrue();

        $this->cache
            ->shouldReceive('get')
            ->with($request)
            ->andReturn(null);

        $this->cache
            ->shouldReceive('put')
            ->with($request, $response);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('Hello world', $result->getContent());
        $this->assertSame(200, $result->getStatusCode());
        $this->assertFalse($result->headers->has('X-Is-Replay'));
    }

    public function test_it_will_not_store_a_response_in_the_cache_based_on_the_strategy(): void
    {
        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $this->strategy
            ->shouldReceive('isIdempotent')
            ->andReturnTrue();

        $this->strategy
            ->shouldReceive('shouldCache')
            ->andReturnFalse();

        $this->cache
            ->shouldReceive('get')
            ->with($request)
            ->andReturn(null);

        $this->cache->shouldNotReceive('put');

        $response = $this->middleware->handle($request, fn () => (new Response())->setStatusCode(400));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Is-Replay'));
    }
}
