<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $admin = $request->user();

        if (!$admin instanceof \App\Models\AdminUser || $admin->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized admin access.',
            ], 401);
        }

        return $next($request);
    }
}
