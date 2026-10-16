<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaff
{
    /**
     * Staf = admin & guru. Siswa ditolak.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! ($user->isAdmin() || $user->isGuru())) {
            abort(403, 'Hanya guru dan admin yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
