<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureBuyerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('buyer')->check()) {
            return redirect()->guest(route('buyer.login'))
                ->with('error', 'Please sign in to access your buyer account.');
        }

        if (Auth::guard('buyer')->user()->status !== 'active') {
            $cart = $request->session()->get('cart');
            Auth::guard('buyer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($cart) {
                $request->session()->put('cart', $cart);
            }

            return redirect()->route('buyer.login')
                ->withErrors(['email' => 'Your buyer account is not active.']);
        }

        return $next($request);
    }
}
