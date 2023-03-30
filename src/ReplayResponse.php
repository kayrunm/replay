<?php

namespace Kayrunm\Replay;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @implements Arrayable<string, mixed>
 */
class ReplayResponse implements Arrayable, Responsable
{
    private string $content;
    private int $status;

    /** @var array<string, string> */
    private array $headers;

    /** @param array<string, string> $headers */
    public function __construct(
        string $content,
        int $status,
        array $headers
    ) {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'status' => $this->status,
            'headers' => $this->headers,
        ];
    }

    public function toResponse($request = null): LaravelResponse
    {
        return (new Response())
            ->setContent($this->content)
            ->setStatusCode($this->status)
            ->withHeaders(array_merge($this->headers, [
                'X-Is-Replay' => 'true',
            ]));
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['content'],
            $data['status'],
            $data['headers']
        );
    }

    public static function fromResponse(SymfonyResponse $response): self
    {
        return new self(
            $response->getContent() ?: '',
            $response->getStatusCode(),
            Arr::except($response->headers->all(), [
                'cache-control',
                'date',
            ])
        );
    }
}
