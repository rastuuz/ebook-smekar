<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NisLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $nis = trim($this->input('nis'));

        $user = User::where('nis', $nis)
            ->where('role', User::ROLE_SISWA)
            ->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->throttleKeyIp());

            throw ValidationException::withMessages([
                'nis' => 'NIS tidak terdaftar. Hubungi admin perpustakaan.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->throttleKeyIp());

        return $user;
    }

    public function ensureIsNotRateLimited(): void
    {
        // Batas per NIS+IP (10) + batas global per IP (30/menit) anti-enumerasi NIS.
        $limited = RateLimiter::tooManyAttempts($this->throttleKey(), 10)
            || RateLimiter::tooManyAttempts($this->throttleKeyIp(), 30);

        if (! $limited) {
            return;
        }

        event(new Lockout($this));

        $seconds = max(
            RateLimiter::availableIn($this->throttleKey()),
            RateLimiter::availableIn($this->throttleKeyIp())
        );

        throw ValidationException::withMessages([
            'nis' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('nis')).'|'.$this->ip());
    }

    public function throttleKeyIp(): string
    {
        return 'nis-login|'.$this->ip();
    }
}
