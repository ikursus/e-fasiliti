<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\LoginAuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function show(Request $request): View
    {
        return view('auth.login', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = $request->user();

        // Absolute session start marker used by the session timeout middleware
        // (NFR-S03), then regenerate the session id to prevent fixation.
        $request->session()->put('auth_authenticated_at', time());
        $request->session()->regenerate();

        $user->fill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        app(LoginAuditLogger::class)->record('login', $user, $request, 'success');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            app(LoginAuditLogger::class)->record('logout', $user, $request, 'success');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
