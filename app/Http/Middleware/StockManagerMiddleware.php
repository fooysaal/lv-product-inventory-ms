<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StockManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && in_array($request->user()->userType->slug, ['stock_manager', 'stock-manager'])) {
            return $next($request);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Stock Managers only.',
            ], 403);
        }
    }
}
