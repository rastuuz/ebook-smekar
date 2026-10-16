<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\NisLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NisLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login-nis');
    }

    public function store(NisLoginRequest $request): RedirectResponse
    {
        $user = $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
