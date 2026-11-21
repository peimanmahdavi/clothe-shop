<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Authenticate the request using a Bearer token stored (hashed) on the user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (blank($token)) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت ارسال نشده است.',
            ], 401);
        }

        $user = User::where('api_token', hash('sha256', $token))->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'توکن احراز هویت نامعتبر است.',
            ], 401);
        }

        if (! $user->is_active) {
            $user->revokeApiToken();

            return response()->json([
                'success' => false,
                'message' => 'حساب شما مسدود شده است.',
            ], 403);
        }

        $request->setUserResolver(fn () => $user);
        auth()->setUser($user);

        return $next($request);
    }
}
