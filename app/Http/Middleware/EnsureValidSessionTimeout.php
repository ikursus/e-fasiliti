<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces an absolute session lifetime (NFR-S03: default 8 hours),
 * independent of the inactivity-based expiry handled by the session driver.
 */
class EnsureValidSessionTimeout
{
    /**
     * Maximum absolute session length in minutes (NFR-S03).
     */
    private const ABSOLUTE_LIFETIME_MINUTES = 480;

    /**
     * Handle the incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only meaningful when there is an authenticated session.
        if (Auth::check()) {
            $authenticatedAt = $request->session()->get('auth_authenticated_at');

            if ($authenticatedAt !== null
                && (time() - (int) $authenticatedAt) >= self::ABSOLUTE_LIFETIME_MINUTES * 60
            ) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with(
                    'status',
                    'Sesi anda telah tamat. Sila log masuk kembali.'
                );
            }
        }

        return $next($request);
    }
}
