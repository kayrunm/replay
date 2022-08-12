<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Kayrunm\Replay\Replay;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class IdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/', function () {
            return 'Hello world!';
        })->middleware(Replay::class);
    }

    public function test_a_request_without_an_idempotency_key_will_run_as_normal(): void
    {
        $this->post('/')->assertOk();
    }

    public function test_a_request_with_an_idempotency_key_which_has_not_been_received_yet_will_run_as_normal(): void
    {
        $this
            ->post('/', [], ['X-Idempotency-Key' => 'Foo'])
            ->assertOk()
            ->assertHeaderMissing('X-Is-Replay');
    }

    public function test_a_request_with_an_idempotency_key_which_has_already_been_received_will_be_replayed(): void
    {
        $this->post('/', [], ['X-Idempotency-Key' => 'Foo']);

        $this
            ->post('/', [], ['X-Idempotency-Key' => 'Foo'])
            ->assertOk()
            ->assertHeader('X-Is-Replay', 'true');
    }

    public function test_a_request_with_an_idempotency_key_which_is_locked_will_return_a_conflict_error(): void
    {
        Cache::lock(md5('Foo:/'))->get();

        $this
            ->post('/', [], ['X-Idempotency-Key' => 'Foo'])
            ->assertStatus(Response::HTTP_CONFLICT);
    }
}
