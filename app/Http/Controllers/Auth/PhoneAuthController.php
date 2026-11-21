<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Http\Requests\LoginWithPasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\CartService;
use App\Services\OtpService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PhoneAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected CartService $cartService,
    ) {}

    /**
     * Show the login/register page (phone number input).
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Send OTP to phone number.
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->sendOtp($request->validated('phone'));

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
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->otpService->verifyOtp($data['phone'], $data['otp']);

        if ($result['success']) {
            /** @var User $user */
            $user = $result['user'];

            if (! $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'حساب شما مسدود شده است.',
                ], 403);
            }

            Auth::login($user, true); // Remember me

            // Merge any guest cart (keyed by the pre-login session id) first.
            $this->cartService->mergeGuestCartIntoUser($user->id);

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
    public function loginWithPassword(LoginWithPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'شماره موبایل یا رمز عبور اشتباه است.',
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حساب شما مسدود شده است.',
            ], 403);
        }

        Auth::login($user, true);

        // Merge any guest cart (keyed by the pre-login session id) first.
        $this->cartService->mergeGuestCartIntoUser($user->id);

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
    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = Auth::user();
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->name = trim("{$data['first_name']} {$data['last_name']}");
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
