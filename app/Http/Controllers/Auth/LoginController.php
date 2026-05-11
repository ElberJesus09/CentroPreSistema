<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, StaffService $staffService): RedirectResponse
    {
        $staff = $staffService->findActiveByUsername($request->validated('username'));

        if ($staff === null || ! Hash::check($request->validated('password'), $staff->getAuthPassword())) {
            return back()
                ->withErrors(['username' => 'Credenciales incorrectas.'])
                ->onlyInput('username');
        }

        Auth::login($staff, $request->boolean('remember'));
        $request->session()->regenerate();
        $staffService->recordLogin($staff);

        return redirect()->intended(route('dashboard'));
    }
}
