<?php

namespace App\Http\Responders\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BaseResponder
{
    public static function sendResponse(array $data = [], array $meta = [], string $message = ''): JsonResponse
    {
        $response = ['success' => true];

        if ($message !== '') {
            $response['message'] = $message;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, ResponseAlias::HTTP_OK, [], JSON_UNESCAPED_UNICODE);
    }

    public static function sendError(string $message = 'Error', array $errors = [], int $code = ResponseAlias::HTTP_BAD_REQUEST): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code, [], JSON_UNESCAPED_UNICODE);
    }

    public static function sendPaginator(LengthAwarePaginator $data): JsonResponse
    {
        $items = $data->items();

        $meta = [
            'page' => $data->currentPage(),
            'perPage' => $data->perPage(),
            'total' => $data->total()
        ];

        return self::sendResponse($items, $meta);
    }
}
