<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Services\SmsSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class OtpAuthController extends Controller
{
    /** How long a code stays valid. */
    private const TTL_SECONDS = 300;

    /** Wrong guesses allowed before the code is burned. */
    private const MAX_ATTEMPTS = 5;

    public function send(Request $request, SmsSender $sms)
    {
        $request->validate([
            'phone' => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
        ], [
            'phone.regex' => 'Enter an 11 digit mobile number starting with 01.',
        ]);

        $phone = $sms->normalise($request->phone);

        // One code per minute per number, and 5 per hour per IP, so the form
        // cannot be used to bulk-send SMS at the shop's expense.
        if (RateLimiter::tooManyAttempts('otp-send:' . $phone, 1)) {
            $wait = RateLimiter::availableIn('otp-send:' . $phone);

            return back()->withErrors([
                'phone' => "Please wait {$wait} seconds before asking for another code.",
            ])->withInput();
        }

        if (RateLimiter::tooManyAttempts('otp-ip:' . $request->ip(), 5)) {
            return back()->withErrors([
                'phone' => 'Too many code requests. Please try again later.',
            ])->withInput();
        }

        $buyer = Buyer::whereIn('phone', $this->phoneVariants($request->phone))
            ->where('status', 'active')
            ->first();

        // Deliberately vague: confirming which numbers have accounts would let
        // anyone enumerate the customer list.
        if (! $buyer) {
            RateLimiter::hit('otp-send:' . $phone, 60);
            RateLimiter::hit('otp-ip:' . $request->ip(), 3600);

            return back()
                ->with('otp_phone', $request->phone)
                ->with('info', 'If that number has an account, a code is on its way.');
        }

        $code = (string) random_int(100000, 999999);

        Cache::put($this->key($phone), [
            'code' => $code,
            'buyer_id' => $buyer->id,
            'attempts' => 0,
        ], self::TTL_SECONDS);

        RateLimiter::hit('otp-send:' . $phone, 60);
        RateLimiter::hit('otp-ip:' . $request->ip(), 3600);

        $sms->send($phone, "Your {$this->shopName()} sign-in code is {$code}. It expires in 5 minutes.");

        return back()
            ->with('otp_phone', $request->phone)
            ->with('success', 'We sent a code to ' . $request->phone . '.');
    }

    public function verify(Request $request, SmsSender $sms)
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $phone = $sms->normalise($request->phone);
        $record = Cache::get($this->key($phone));

        if (! $record) {
            return back()
                ->with('otp_phone', $request->phone)
                ->withErrors(['code' => 'That code has expired. Please request a new one.']);
        }

        if ($record['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($this->key($phone));

            return back()->withErrors(['code' => 'Too many wrong attempts. Please request a new code.']);
        }

        if (! hash_equals($record['code'], $request->code)) {
            $record['attempts']++;
            // Keep the original expiry rather than extending it on each guess.
            Cache::put($this->key($phone), $record, self::TTL_SECONDS);

            return back()
                ->with('otp_phone', $request->phone)
                ->withErrors(['code' => 'That code is not correct.']);
        }

        Cache::forget($this->key($phone));

        $buyer = Buyer::where('status', 'active')->find($record['buyer_id']);

        if (! $buyer) {
            return back()->withErrors(['code' => 'This account is no longer active.']);
        }

        $buyer->forceFill(['phone_verified_at' => now()])->save();

        Auth::guard('buyer')->login($buyer, true);
        $request->session()->regenerate();

        return redirect()->intended(route('buyer.dashboard'))->with('success', 'Welcome back.');
    }

    /**
     * Numbers may be stored as 01712345678 or 8801712345678 depending on how the
     * account was created, so look for both.
     */
    private function phoneVariants(string $input): array
    {
        $digits = preg_replace('/\D/', '', $input);
        $local = str_starts_with($digits, '880') ? substr($digits, 2) : $digits;

        return array_unique([$input, $local, '88' . $local, '+88' . $local]);
    }

    private function key(string $normalisedPhone): string
    {
        return 'buyer-otp:' . $normalisedPhone;
    }

    private function shopName(): string
    {
        return optional(\App\Models\Setting::first())->title ?: config('app.name');
    }
}
