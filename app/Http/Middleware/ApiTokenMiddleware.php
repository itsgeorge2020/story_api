<?php

namespace App\Http\Middleware;

use App\Http\Responders\Api\V1\BaseResponder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('app.api.token');
        $provided = (string) $request->header('Token');

        if ($expected === '' || !hash_equals($expected, $provided)) {
            return BaseResponder::sendError('Unauthorized', [], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
