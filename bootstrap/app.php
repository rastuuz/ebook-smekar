<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSiswa;
use App\Http\Middleware\EnsureStaff;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'siswa' => EnsureSiswa::class,
            'staff' => EnsureStaff::class,
        ]);

        // Tamu yang membuka halaman butuh login diarahkan ke login NIS (utama).
        $middleware->redirectGuestsTo(fn () => route('login.nis'));
        // Pengguna yang sudah login dan membuka halaman tamu (login) diarahkan ke dashboard.
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
