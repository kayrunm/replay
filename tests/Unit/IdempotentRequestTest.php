<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Kayrunm\Replay\Cache\Repository;
use Kayrunm\Replay\IdempotentRequest;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class IdempotentRequestTest extends TestCase
{
    private MockInterface $repository;
    private IdempotentRequest $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(Repository::class);

        $this->middleware = new IdempotentRequest(
            $this->repository
        );
    }

    public function test_requests_without_an_idempotency_key_run_as_normal(): void
    {
        $this->middleware->handle(new Request(), fn () => $this->assertTrue(true));
    }

    public function test_returns_a_response_from_the_cache(): void
    {
        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $this->repository
            ->expects('get')
            ->once()
            ->with($request)
            ->andReturn([
                'content' => 'Hello world',
                'status' => 200,
                'headers' => ['X-Foo' => 'bar'],
            ]);

        $response = $this->middleware->handle($request, function () {});

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('Hello world', $response->getContent());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('bar', $response->headers->get('X-Foo'));
        $this->assertSame('true', $response->headers->get('X-Is-Replay'));
    }

    public function test_it_stores_an_ok_response_in_the_cache(): void
    {
        Carbon::setTestNow('2022-07-31 09:30:00');

        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $response = (new Response())
            ->setContent('Hello world')
            ->setStatusCode(200);

        $this->repository
            ->expects('get')
            ->once()
            ->with($request)
            ->andReturn(null);

        $this->repository
            ->expects('put')
            ->once()
            ->with($request, $response);

        $result = $this->middleware->handle($request, fn () => $response);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('Hello world', $result->getContent());
        $this->assertSame(200, $result->getStatusCode());
        $this->assertFalse($result->headers->has('X-Is-Replay'));
    }

    public function test_an_error_response_is_not_stored_in_the_cache(): void
    {
        $request = new Request();
        $request->headers->add(['X-Idempotency-Key' => 'abc']);

        $this->repository
            ->expects('get')
            ->once()
            ->with($request)
            ->andReturn(null);

        $this->repository->expects('put')->never();

        $response = $this->middleware->handle($request, fn () => (new Response())->setStatusCode(400));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Is-Replay'));
    }
}
