<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // Siswa login via NIS tanpa password — tolak dengan pesan ramah,
        // bukan 422 current_password yang membingungkan.
        if (empty($request->user()->password)) {
            return back()->withErrors(
                ['current_password' => 'Akun siswa tidak memakai password. Hubungi admin jika butuh bantuan.'],
                'updatePassword'
            );
        }

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
