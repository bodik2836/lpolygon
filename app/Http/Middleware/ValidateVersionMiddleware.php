<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateVersionMiddleware
{
    const ACTUAL_VERSION = '1.0.0';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->get('version')) {
            return response()->json([
                'message' => 'Provide version parameter for this route.'
            ]);
        }

        if ($request->get('version') !== self::ACTUAL_VERSION) {
            return response()->json([
                'message' => 'Upgrade version to actual value.'
            ]);
        }

        return $next($request);
    }
}
