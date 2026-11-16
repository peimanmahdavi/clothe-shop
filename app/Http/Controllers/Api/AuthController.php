<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
        ]);

        $result = $this->otpService->sendOtp($request->phone);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'debug_otp' => $result['debug_otp'] ?? null,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'otp' => ['required', 'digits:6'],
        ]);

        $result = $this->otpService->verifyOtp($request->phone, $request->otp);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        /** @var User $user */
        $user = $result['user'];
        $token = $user->generateApiToken();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'token' => $token,
            'token_type' => 'Bearer',
            'needs_profile' => blank($user->first_name) || blank($user->last_name),
            'user' => $this->userPayload($user),
        ]);
    }

    public function completeProfile(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = trim("{$user->first_name} {$user->last_name}");
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت تکمیل شد.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->revokeApiToken();

        return response()->json([
            'success' => true,
            'message' => 'با موفقیت خارج شدید.',
        ]);
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'is_admin' => $user->is_admin,
        ];
    }
}