<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in is not configured yet.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            // Covers the user cancelling at Google's consent screen as well as
            // a bad client secret — either way there is nothing to sign in with.
            Log::warning('Google sign-in failed: ' . $e->getMessage());

            return redirect()->route('login')
                ->with('error', 'We could not complete Google sign-in. Please try again.');
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')
                ->with('error', 'Your Google account did not share an email address, so we cannot sign you in.');
        }

        // Match on the Google id first, then fall back to email so an existing
        // password account gets linked instead of duplicated.
        $buyer = Buyer::where('google_id', $googleUser->getId())->first()
            ?? Buyer::where('email', $email)->first();

        if ($buyer && $buyer->status !== 'active') {
            return redirect()->route('login')
                ->with('error', 'This account is not active. Please contact us for help.');
        }

        if ($buyer) {
            $buyer->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $buyer->avatar ?: $googleUser->getAvatar(),
            ])->save();
        } else {
            $buyer = Buyer::create([
                'company_id' => Company::query()->orderBy('id')->value('id'),
                'business_name' => $googleUser->getName() ?: Str::before($email, '@'),
                'category' => 'Retail',
                'email' => $email,
                // Never left blank: a random password keeps the column valid while
                // must_set_password lets the account set a real one later.
                'password' => Str::random(40),
                'must_set_password' => true,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'status' => 'active',
                'verification_log' => ['source' => 'google_oauth'],
            ]);
        }

        Auth::guard('buyer')->login($buyer, true);
        $request->session()->regenerate();

        return redirect()->intended(route('buyer.dashboard'))
            ->with('success', 'Signed in with Google.');
    }
}
