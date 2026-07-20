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
        // Skip Livewire background requests to avoid blocking AJAX updates
        if ($request->routeIs('livewire.*')) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user) {
            $currentRoute = $request->route() ? $request->route()->getName() : '';

            // Case 1: User has NOT set up / confirmed 2FA yet. Force them to settings.
            if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
                if ($currentRoute !== 'filament.app.pages.two-factor-settings' &&
                    $currentRoute !== 'filament.app.auth.logout') {
                    return redirect()->route('filament.app.pages.two-factor-settings');
                }
            } 
            // Case 2: User has configured 2FA but is not verified for this session.
            else {
                if ($currentRoute !== 'filament.app.pages.two-factor-verify' &&
                    $currentRoute !== 'filament.app.auth.logout' &&
                    !session()->get('two_factor_verified')) {
                    return redirect()->route('filament.app.pages.two-factor-verify');
                }
            }
        }

        return $next($request);
    }
}
