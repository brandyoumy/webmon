<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If the user has confirmed 2FA setup but is not verified for this session
        if ($user && $user->two_factor_secret && $user->two_factor_confirmed_at) {
            $currentRoute = $request->route() ? $request->route()->getName() : '';

            // Allow access to the 2FA verification page and logout route, redirect other routes
            if ($currentRoute !== 'filament.app.pages.two-factor-verify' &&
                $currentRoute !== 'filament.app.auth.logout' &&
                !session()->get('two_factor_verified')) {
                
                return redirect()->route('filament.app.pages.two-factor-verify');
            }
        }

        return $next($request);
    }
}
