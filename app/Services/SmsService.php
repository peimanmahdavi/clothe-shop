<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SmsService
{
    /**
     * Send an SMS message using the configured driver.
     */
    public function send(string $phone, string $message): bool
    {
        $driver = Config::get('services.sms.driver', 'log');

        return match ($driver) {
            'kavenegar' => $this->sendViaKavenegar($phone, $message),
            'smsir' => $this->sendViaSmsIr($phone, $message),
            default => $this->sendViaLog($phone, $message),
        };
    }

    /**
     * Send an OTP message to the given phone number.
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $appName = Config::get('app.name', 'Cloth Shop');
        $message = "کد ورود شما به {$appName}: {$otp}\nاین کد تا ۵ دقیقه معتبر است.";

        return $this->send($phone, $message);
    }

    /**
     * Debug driver: logs the message instead of sending it.
     */
    protected function sendViaLog(string $phone, string $message): bool
    {
        Log::debug('SMS (log driver)', [
            'to' => $phone,
            'message' => $message,
        ]);

        return true;
    }

    /**
     * Send via Kavenegar (https://kavenegar.com).
     */
    protected function sendViaKavenegar(string $phone, string $message): bool
    {
        $apiKey = Config::get('services.sms.kavenegar.api_key');

        if (blank($apiKey)) {
            Log::warning('SMS: Kavenegar API key is not configured.', ['to' => $phone]);
            return false;
        }

        try {
            $response = Http::timeout(10)->asForm()->post(
                "https://api.kavenegar.com/v1/{$apiKey}/sms/send.json",
                [
                    'receptor' => $phone,
                    'message' => $message,
                    'sender' => Config::get('services.sms.kavenegar.sender'),
                ]
            );

            if ($response->failed()) {
                Log::error('SMS: Kavenegar request failed.', [
                    'to' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS: Kavenegar exception.', ['to' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send via SMS.ir (https://sms.ir).
     */
    protected function sendViaSmsIr(string $phone, string $message): bool
    {
        $apiKey = Config::get('services.sms.smsir.api_key');

        if (blank($apiKey)) {
            Log::warning('SMS: SMS.ir API key is not configured.', ['to' => $phone]);
            return false;
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])->post('https://api.sms.ir/v1/send/bulk', [
                'lineNumber' => Config::get('services.sms.smsir.line_number'),
                'messageText' => $message,
                'mobiles' => [$phone],
            ]);

            if ($response->failed()) {
                Log::error('SMS: SMS.ir request failed.', [
                    'to' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS: SMS.ir exception.', ['to' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }
}