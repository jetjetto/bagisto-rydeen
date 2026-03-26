<?php

namespace Rydeen\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStandardAuth
{
    /**
     * Redirect standard Bagisto auth/storefront routes to dealer portal.
     *
     * Catches requests to the storefront home, customer auth pages,
     * and company registration, redirecting them to the dealer portal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Root path → dealer dashboard (or login if unauthenticated)
        if ($path === '/' || $path === '') {
            return auth('customer')->check()
                ? redirect()->route('dealer.dashboard')
                : redirect()->route('dealer.login');
        }

        $redirectPaths = [
            'customer/login',
            'customer/register',
            'customer/forgot-password',
            'companies/register',
        ];

        if (in_array($path, $redirectPaths)) {
            return redirect()->route('dealer.login');
        }

        return $next($request);
    }
}
