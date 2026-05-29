<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogService;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, StaffService $staffService, ActivityLogService $activityLogService): RedirectResponse
    {
        $staff = $staffService->findActiveByUsername($request->validated('username'));

        if ($staff === null || ! Hash::check($request->validated('password'), $staff->getAuthPassword())) {
            Log::channel('security')->warning('Intento fallido de inicio de sesion.', [
                'username' => $request->validated('username'),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            ]);

            return back()
                ->withErrors(['username' => 'Credenciales incorrectas.'])
                ->onlyInput('username');
        }

        if (
            ! $staff->can('dashboard.view')
            && ! $staff->canAccessStudentsModule()
            && ! $staff->canAccessStaffModule()
            && ! $staff->canAccessAcademicCyclesModule()
            && ! $staff->canAccessAcademicManagementModule()
            && ! $staff->canAccessReportsModule()
        ) {
            return back()
                ->withErrors(['username' => 'Su usuario no tiene acceso al panel administrativo.'])
                ->onlyInput('username');
        }

        Auth::login($staff, $request->boolean('remember'));
        $request->session()->regenerate();
        $staffService->recordLogin($staff);
        $activityLogService->record('auth', 'login', 'Inicio sesion en el panel administrativo.', staff: $staff, request: $request);

        return redirect()->intended(route('dashboard'));
    }
}
