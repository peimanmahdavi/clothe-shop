<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->is_admin && $user->is_active) {
            return $next($request);
        }

        return response()->view('errors.page', [
            'status' => 403,
            'title' => 'دسترسی غیرمجاز',
            'message' => 'شما اجازه دسترسی به این بخش را ندارید.',
            'hint' => 'در صورت نیاز با مدیر فروشگاه تماس بگیرید.',
        ], 403);
    }
}
