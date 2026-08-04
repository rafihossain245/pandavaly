<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper over whatever SMS gateway the shop uses.
 *
 * The "log" driver is the default so OTP sign-in works end to end in
 * development without credentials — the code lands in laravel.log. Point
 * SMS_DRIVER at "http" and fill in SMS_ENDPOINT / SMS_API_KEY / SMS_SENDER_ID
 * to send for real; most Bangladeshi gateways (Alpha SMS, BulkSMSBD, MiMSMS,
 * SSLWireless) accept exactly this shape of GET/POST request.
 */
class SmsSender
{
    public function send(string $phone, string $message): bool
    {
        $driver = config('services.sms.driver', 'log');

        if ($driver === 'log') {
            Log::info('[SMS:log] would send to ' . $phone . ': ' . $message);

            return true;
        }

        $endpoint = config('services.sms.endpoint');

        if (! $endpoint) {
            Log::error('[SMS] SMS_DRIVER is set but SMS_ENDPOINT is empty; message not sent.');

            return false;
        }

        try {
            $response = Http::timeout(15)->asForm()->post($endpoint, [
                'api_key' => config('services.sms.api_key'),
                'senderid' => config('services.sms.sender_id'),
                'msisdn' => $this->normalise($phone),
                'message' => $message,
            ]);

            if ($response->failed()) {
                Log::error('[SMS] gateway rejected the message', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('[SMS] gateway unreachable: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Bangladeshi numbers are typed as 01XXXXXXXXX but most gateways want
     * 8801XXXXXXXXX. Accepts either and returns the international form.
     */
    public function normalise(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '880')) {
            return $digits;
        }

        return '88' . ltrim($digits, '+');
    }
}
