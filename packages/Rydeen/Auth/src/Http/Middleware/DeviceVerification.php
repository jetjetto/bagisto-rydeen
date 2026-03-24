<?php

namespace Rydeen\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Rydeen\Auth\Services\AuthService;
use Symfony\Component\HttpFoundation\Response;

class DeviceVerification
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Handle an incoming request.
     *
     * Checks that the customer is authenticated and that the device cookie
     * maps to a valid (non-expired) trusted device record.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return redirect()->route('dealer.login');
        }

        $uuid = $request->cookie('rydeen_device');

        if ($this->authService->isDeviceTrusted($customer, $uuid)) {
            return $next($request);
        }

        return redirect()->route('dealer.login');
    }
}
