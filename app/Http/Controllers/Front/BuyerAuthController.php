<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use App\Models\BuyerDocument;
use App\Models\Company;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class BuyerAuthController extends Controller
{
    public function showLogin()
    {
        return view('frontEnd.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['status'] = 'active';

        if (! Auth::guard('buyer')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'The provided credentials do not match an active buyer account.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('buyer.dashboard'))
            ->with('success', 'Welcome back.');
    }

    public function showRegister()
    {
        return view('frontEnd.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:buyers,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'tin' => ['nullable', 'string', 'max:50'],
            'trade_license_no' => ['nullable', 'string', 'max:100'],
            'trade_license_expiry' => ['nullable', 'date'],
            'trade_license_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $company = Company::query()->orderBy('id')->first();

        $buyer = DB::transaction(function () use ($validated, $company, $request) {
            $buyer = Buyer::create([
                'company_id' => $company?->id,
                'business_name' => $validated['business_name'],
                'category' => $validated['category'] ?? 'Retail',
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'status' => 'active',
                'tin' => $validated['tin'] ?? null,
                'trade_license_no' => $validated['trade_license_no'] ?? null,
                'trade_license_expiry' => $validated['trade_license_expiry'] ?? null,
                'verification_log' => ['source' => 'buyer_registration'],
            ]);

            $buyer->contacts()->create([
                'name' => $buyer->business_name,
                'email' => $buyer->email,
                'phone' => $buyer->phone,
                'designation' => 'Primary Contact',
                'is_primary' => true,
            ]);

            if ($request->hasFile('trade_license_document')) {
                $file = $request->file('trade_license_document');
                $filename = uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                $uploadPath = 'images/buyer-documents/';
                $file->move(public_path($uploadPath), $filename);

                $media = MediaFile::create([
                    'mediable_type' => Buyer::class,
                    'mediable_id' => $buyer->id,
                    'disk' => 'public',
                    'path' => $uploadPath . $filename,
                    'mime' => $file->getClientMimeType(),
                    'title' => 'Trade License Document',
                    'uploaded_by' => null,
                ]);

                BuyerDocument::create([
                    'buyer_id' => $buyer->id,
                    'type' => 'TradeLicense',
                    'number' => $validated['trade_license_no'] ?? null,
                    'expiry_date' => $validated['trade_license_expiry'] ?? null,
                    'media_id' => $media->id,
                ]);
            }

            return $buyer;
        });

        Auth::guard('buyer')->login($buyer);
        $request->session()->regenerate();

        return redirect()->intended(route('buyer.dashboard'))
            ->with('success', 'Your buyer account has been created.');
    }

    public function logout(Request $request)
    {
        $cart = $request->session()->get('cart');
        Auth::guard('buyer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($cart) {
            $request->session()->put('cart', $cart);
        }

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }
}
