<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Halaman login admin (username + password, khusus role admin).
     */
    public function createAdmin(): View
    {
        return view('auth.login-admin');
    }

    public function storeAdmin(StaffLoginRequest $request): RedirectResponse
    {
        $request->expectedRole = User::ROLE_ADMIN;
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    /**
     * Halaman login guru (username + password, khusus role guru).
     */
    public function createGuru(): View
    {
        return view('auth.login-guru');
    }

    public function storeGuru(StaffLoginRequest $request): RedirectResponse
    {
        $request->expectedRole = User::ROLE_GURU;
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
