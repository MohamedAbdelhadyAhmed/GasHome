<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->tokenCan('role:user')) {
            return $next($request);
        }
        // return  $this->error_message('Not Authorized',[], 401);
        return response()->json([
            'status' => false,
            'message' => 'Not Authorized',
            'data' => [],

        ]); // return response()->json('Not Authorized', 401);
    }
}
