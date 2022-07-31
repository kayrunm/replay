<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Kayrunm\Replay\IdempotentRequest;
use Tests\TestCase;

class IdempotentRequestTest extends TestCase
{
    public function test_requests_without_an_idempotency_key_run_as_normal(): void
    {
        (new IdempotentRequest())->handle(new Request(), function () {
            $this->assertTrue(true);
        });
    }

    public function test_returns_a_response_from_the_cache(): void
    {
        Cache::shouldReceive('has')
            ->once()
            ->with($key = md5('abc:/'))
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->with($key)
            ->andReturn([
                'content' => 'Hello world',
                'status' => 200,
                'headers' => ['X-Foo' => 'bar'],
            ]);

        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $response = (new IdempotentRequest())->handle($request, function () {});

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('Hello world', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('bar', $response->headers->get('X-Foo'));
        $this->assertSame('true', $response->headers->get('X-Is-Replay'));
    }

    public function test_it_stores_an_ok_response_in_the_cache(): void
    {
        Carbon::setTestNow('2022-07-31 09:30:00');

        Cache::shouldReceive('has')
            ->once()
            ->with($hash = md5('abc:/'))
            ->andReturn(false);

        Cache::shouldReceive('put')->once()->withArgs(function (
            string $key,
            array $value,
            Carbon $expiry
        ) use ($hash) {
            return $key === $hash
                && $value['content'] === 'Hello world'
                && $value['status'] === 200
                && $expiry->is('2022-08-01 09:30:00');
        });

        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $response = (new IdempotentRequest())->handle($request, function () {
            return (new Response())
                ->setContent('Hello world')
                ->setStatusCode(200);
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('Hello world', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Is-Replay'));
    }

    public function test_an_error_response_is_not_stored_in_the_cache(): void
    {
        Cache::shouldReceive('has')
            ->once()
            ->with(md5('abc:/'))
            ->andReturn(false);

        Cache::shouldReceive('put')->never();

        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $response = (new IdempotentRequest())->handle($request, function () {
            return (new Response())->setStatusCode(400);
        });

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Is-Replay'));
    }
}
