<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Banned (inactive) accounts are logged out immediately and cannot reach any
 * authenticated area — including a session that was issued before the ban.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'حساب شما مسدود شده است.',
                ], 403);
            }

            return response()->view('errors.page', [
                'status' => 403,
                'title' => 'حساب مسدود',
                'message' => 'حساب شما توسط مدیر فروشگاه مسدود شده است.',
                'hint' => 'در صورت نیاز با مدیر فروشگاه تماس بگیرید.',
            ], 403);
        }

        return $next($request);
    }
}
