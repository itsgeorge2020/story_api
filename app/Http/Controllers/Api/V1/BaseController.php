<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responders\Api\V1\BaseResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BaseController extends Controller
{
    public const DEFAULT_PAGINATE_LIMIT = 20;

    public function sendResponse(array $data = [], array $meta = [], string $message = ''): JsonResponse
    {
        return BaseResponder::sendResponse($data, $meta, $message);
    }

    public function sendError(string $message = 'Error', array $errors = [], int $code = ResponseAlias::HTTP_BAD_REQUEST): JsonResponse
    {
        return BaseResponder::sendError($message, $errors, $code);
    }

    public function sendPaginator(LengthAwarePaginator $data): JsonResponse
    {
        return BaseResponder::sendPaginator($data);
    }
}
