<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteProfileRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->sendOtp($request->validated('phone'));

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'debug_otp' => $result['debug_otp'] ?? null,
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $result = $this->otpService->verifyOtp($data['phone'], $data['otp']);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        /** @var User $user */
        $user = $result['user'];

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'حساب شما مسدود شده است.',
            ], 403);
        }

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

    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $request->user();
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->name = trim("{$data['first_name']} {$data['last_name']}");
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

    /**
     * @return array<string, mixed>
     */
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
