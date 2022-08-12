<?php

namespace Kayrunm\Replay\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

final class MatchingRequestStillExecuting extends Exception
{
    private Request $request;

    public function __construct(Request $request)
    {
        parent::__construct('Another request with the same idempotency key is currently executing.');

        $this->request = $request;
    }

    public function render(): BaseResponse
    {
        if ($this->request->wantsJson()) {
            return new JsonResponse([
                'error' => $this->getMessage(),
            ], BaseResponse::HTTP_CONFLICT);
        }

        return new Response($this->getMessage(), BaseResponse::HTTP_CONFLICT);
    }

    public static function for(Request $request): self
    {
        return new self($request);
    }
}
