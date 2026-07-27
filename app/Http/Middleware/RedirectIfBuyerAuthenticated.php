<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfBuyerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('buyer')->check()) {
            return redirect()->route('buyer.dashboard');
        }

        return $next($request);
    }
}
