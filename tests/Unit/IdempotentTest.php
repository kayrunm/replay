<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kayrunm\Replay\Cache\Repository;
use Kayrunm\Replay\Idempotent;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class IdempotentTest extends TestCase
{
    private Idempotent $middleware;

    /** @var Repository|MockObject */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(Repository::class);

        $this->middleware = new Idempotent(
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
            ->expects($this->once())
            ->method('get')
            ->with($request)
            ->willReturn([
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
            ->expects($this->once())
            ->method('get')
            ->with($request)
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('put')
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
            ->expects($this->once())
            ->method('get')
            ->with($request)
            ->willReturn(null);

        $this->repository
            ->expects($this->never())
            ->method('put');

        $response = $this->middleware->handle($request, fn () => (new Response())->setStatusCode(400));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($response->headers->has('X-Is-Replay'));
    }
}
