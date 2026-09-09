<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!in_array($request->user()->role, $roles)){
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak, anda tidak memiliki izin.',
                'errors' => ['role' => ['Membutuhkan Privilage yang lebih tinggi.']]
            ], 403);
        }

        return $next($request);
    }
}
