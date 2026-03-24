<?php

namespace Rydeen\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStandardAuth
{
    /**
     * Redirect standard Bagisto auth routes to the dealer login.
     *
     * Catches requests to /customer/login, /customer/register,
     * and /companies/register and redirects them to /dealer/login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        $redirectPaths = [
            'customer/login',
            'customer/register',
            'companies/register',
        ];

        if (in_array($path, $redirectPaths)) {
            return redirect()->route('dealer.login');
        }

        return $next($request);
    }
}
