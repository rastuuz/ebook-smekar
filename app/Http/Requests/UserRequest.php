<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        // String kosong dianggap NULL agar tidak bentrok dengan unique.
        foreach (['nis', 'username', 'email', 'class'] as $key) {
            if ($this->has($key) && trim((string) $this->input($key)) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isGuru = $this->input('role') === User::ROLE_GURU;
        $isStore = $this->isMethod('post');

        // Saat ubah ke guru: password wajib jika akun belum punya password.
        $guruNeedsPassword = $isGuru && ($isStore || empty($this->route('user')?->password));

        return [
            // Akun admin tidak boleh dibuat/diubah dari sini.
            'role' => ['required', Rule::in([User::ROLE_SISWA, User::ROLE_GURU])],
            'name' => ['required', 'string', 'max:255'],
            // Siswa: NIS wajib, kelas opsional (file sekolah sering cuma NIS+Nama).
            // Guru: username + password wajib.
            'nis' => $isGuru
                ? ['nullable', 'string', 'max:20', Rule::unique('users', 'nis')->ignore($userId)]
                : ['required', 'string', 'max:20', Rule::unique('users', 'nis')->ignore($userId)],
            'class' => ['nullable', 'string', 'max:50'],
            'username' => $isGuru
                ? ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)]
                : ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => $guruNeedsPassword
                ? ['required', 'string', 'min:8', 'max:255']
                : ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi untuk siswa.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'username.required' => 'Username wajib diisi untuk guru.',
            'username.unique' => 'Username sudah dipakai.',
            'name.required' => 'Nama wajib diisi.',
            'password.required' => 'Password wajib diisi untuk akun guru.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
