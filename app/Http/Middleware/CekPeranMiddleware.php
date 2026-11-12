<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPeranMiddleware
{
    /**
     * Memeriksa apakah peran user yang login sesuai dengan daftar peran yang diizinkan.
     */
    public function handle(Request $request, Closure $next, string ...$peranDiizinkan): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->peran, $peranDiizinkan)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}