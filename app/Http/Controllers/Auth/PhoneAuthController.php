<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PhoneAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    /**
     * Show the login/register page (phone number input).
     */
    public function showLogin(): \Illuminate\Contracts\View\View
    {
        return view('auth.login');
    }

    /**
     * Send OTP to phone number.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
        ]);

        $result = $this->otpService->sendOtp($request->phone);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'debug_otp' => $result['debug_otp'] ?? null,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    /**
     * Verify OTP and log in user.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'otp' => ['required', 'digits:6'],
        ]);

        $result = $this->otpService->verifyOtp($request->phone, $request->otp);

        if ($result['success']) {
            $user = $result['user'];
            Auth::login($user, true); // Remember me

            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => $user->is_admin ? route('admin.dashboard') : route('profile.dashboard'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'is_admin' => $user->is_admin,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 422);
    }

    /**
     * Log in using phone + password (alternative to OTP).
     */
    public function loginWithPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! $user->password || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'شماره موبایل یا رمز عبور اشتباه است.',
            ], 422);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'با موفقیت وارد شدید.',
            'redirect' => $user->is_admin ? route('admin.dashboard') : route('profile.dashboard'),
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'با موفقیت خارج شدید.');
    }

    /**
     * Complete user profile (name, last name).
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
        ]);

        $user = Auth::user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = trim("{$request->first_name} {$request->last_name}");
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت تکمیل شد.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
        ]);
    }
}