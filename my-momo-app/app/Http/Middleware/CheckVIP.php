<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckVIP
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isVip()) {
            return $next($request);
        }

        return response()->json(['message' => 'Bạn không có quyền truy cập VIP.'], 403);
    }
}
