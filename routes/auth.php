<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NisLoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Login siswa dengan NIS (tanpa password)
Route::middleware('guest')->group(function () {
    Route::get('login-siswa', [NisLoginController::class, 'create'])
        ->name('login.nis');

    Route::post('login-siswa', [NisLoginController::class, 'store'])
        ->name('login.nis.store');

    // Login admin dan guru dipisah (username + password, sesuai role).
    Route::get('login-admin', [AuthenticatedSessionController::class, 'createAdmin'])
        ->name('login.admin');

    Route::post('login-admin', [AuthenticatedSessionController::class, 'storeAdmin'])
        ->name('login.admin.store');

    Route::get('login-guru', [AuthenticatedSessionController::class, 'createGuru'])
        ->name('login.guru');

    Route::post('login-guru', [AuthenticatedSessionController::class, 'storeGuru'])
        ->name('login.guru.store');

    // Reset password via email DINONAKTIFKAN: semua user email=null (siswa NIS,
    // guru/admin username). Ganti password lewat menu Profil (current_password).
    // Butuh reset manual? Gunakan tinker: $u->password='baru'; $u->save();
});

// Registrasi mandiri dinonaktifkan: siswa dikelola admin via UserSeeder / CRUD admin.
// Jika butuh registrasi admin awal, gunakan seeder atau tinker.

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
