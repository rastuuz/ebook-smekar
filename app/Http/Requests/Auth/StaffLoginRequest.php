<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StaffLoginRequest extends FormRequest
{
    /**
     * Role yang boleh masuk lewat request ini.
     * Diisi controller sebelum authenticate() dipanggil.
     * null = semua staf (admin & guru).
     */
    public ?string $expectedRole = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('username', trim($this->input('username')))->first();

        if (! $user || ! $user->password || ! Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->throttleKeyIp());

            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        if (! $user->isStaff()) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->throttleKeyIp());

            throw ValidationException::withMessages([
                'username' => 'Akun ini tidak punya akses staf. Siswa silakan login dengan NIS.',
            ]);
        }

        if ($this->expectedRole !== null && $user->role !== $this->expectedRole) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->throttleKeyIp());

            $portal = $this->expectedRole === User::ROLE_ADMIN ? 'admin' : 'guru';

            throw ValidationException::withMessages([
                'username' => "Akun ini bukan akun {$portal}. Silakan gunakan halaman login {$portal}.",
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->throttleKeyIp());

        return $user;
    }

    public function ensureIsNotRateLimited(): void
    {
        // Batas per username+IP (5) + batas global per IP (20/menit) anti-brute force.
        $limited = RateLimiter::tooManyAttempts($this->throttleKey(), 5)
            || RateLimiter::tooManyAttempts($this->throttleKeyIp(), 20);

        if (! $limited) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(
            RateLimiter::availableIn($this->throttleKey()),
            RateLimiter::availableIn($this->throttleKeyIp())
        );

        throw ValidationException::withMessages([
            'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')).'|'.$this->ip());
    }

    public function throttleKeyIp(): string
    {
        return 'staff-login|'.$this->ip();
    }
}
