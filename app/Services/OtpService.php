<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public const OTP_TTL_SECONDS = 300; // 5 minutes
    public const OTP_RATE_LIMIT_KEY = 'otp_rate_limit:';
    public const MAX_OTP_REQUESTS_PER_HOUR = 5;

    public function __construct(
        protected SmsService $sms
    ) {}

    /**
     * Send OTP via SMS to the user's phone number.
     *
     * For debugging in local/testing environments the OTP is always 123456
     * (see User::generateOtp()) and the message is written to the log.
     */
    public function sendOtp(string $phone): array
    {
        // Rate limiting
        $rateLimitKey = self::OTP_RATE_LIMIT_KEY . $phone;
        $requests = Cache::get($rateLimitKey, 0);

        if ($requests >= self::MAX_OTP_REQUESTS_PER_HOUR) {
            return [
                'success' => false,
                'message' => 'تعداد درخواست‌های ارسال کد احراز هویت از حد مجاز گذشته است. لطفاً یک ساعت دیگر تلاش کنید.',
            ];
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'کاربر ' . substr($phone, -4)]
        );

        // Generate OTP
        $otp = $user->generateOtp();

        // Increment rate limit counter (set with a 1-hour TTL on first request)
        Cache::add($rateLimitKey, 0, 3600);
        Cache::increment($rateLimitKey);

        // Send SMS (or log in debug mode)
        $sent = $this->sms->sendOtp($phone, $otp);

        if ($sent) {
            Log::info('OTP sent', [
                'phone' => $phone,
                'otp' => config('app.debug') ? $otp : '***',
            ]);

            return [
                'success' => true,
                'message' => 'کد احراز هویت با موفقیت ارسال شد.',
                'debug_otp' => config('app.debug') ? $otp : null,
            ];
        }

        return [
            'success' => false,
            'message' => 'خطا در ارسال پیامک. لطفاً مجدداً تلاش کنید.',
        ];
    }

    /**
     * Verify OTP for a phone number.
     */
    public function verifyOtp(string $phone, string $otp): array
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'کاربری با این شماره تلفن یافت نشد.',
            ];
        }

        if ($user->verifyOtp($otp)) {
            return [
                'success' => true,
                'message' => 'احراز هویت با موفقیت انجام شد.',
                'user' => $user,
            ];
        }

        return [
            'success' => false,
            'message' => 'کد احراز هویت نامعتبر یا منقضی شده است.',
        ];
    }

    /**
     * Check if user can request a new OTP (rate limiting).
     */
    public function canRequestOtp(string $phone): bool
    {
        $rateLimitKey = self::OTP_RATE_LIMIT_KEY . $phone;
        $requests = Cache::get($rateLimitKey, 0);
        return $requests < self::MAX_OTP_REQUESTS_PER_HOUR;
    }

    /**
     * Get remaining time until next OTP request allowed.
     */
    public function getRateLimitRemainingTime(string $phone): int
    {
        $rateLimitKey = self::OTP_RATE_LIMIT_KEY . $phone;
        return Cache::get($rateLimitKey . ':ttl', 0);
    }
}