<?php

namespace Rydeen\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rydeen\Auth\Services\AuthService;

class LoginController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Show the dealer login form.
     */
    public function showLogin()
    {
        if (auth('customer')->check()) {
            return redirect()->route('dealer.dashboard');
        }

        return view('rydeen-auth::login');
    }

    /**
     * Authenticate with email + password (simplified for MVP).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! auth('customer')->attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ])) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }

        $customer = auth('customer')->user();

        if (! $customer->is_verified) {
            auth('customer')->logout();
            return back()->withErrors(['email' => 'Your account is not yet approved.'])->withInput();
        }

        if ($customer->is_suspended) {
            auth('customer')->logout();
            return back()->withErrors(['email' => 'Your account has been suspended.'])->withInput();
        }

        return redirect()->route('dealer.dashboard');
    }

    /**
     * Send a verification code to the given email (for future passwordless flow).
     */
    public function sendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $result = $this->authService->generateCode($request->email);

        if (! $result['success']) {
            return back()->withErrors(['email' => $result['error']])->withInput();
        }

        session(['rydeen_verify_email' => $request->email]);

        return redirect()->route('dealer.verify.form');
    }

    /**
     * Show the verification code entry form.
     */
    public function showVerify()
    {
        if (! session('rydeen_verify_email')) {
            return redirect()->route('dealer.login');
        }

        return view('rydeen-auth::verify', ['email' => session('rydeen_verify_email')]);
    }

    /**
     * Verify the 6-digit code and authenticate the customer.
     */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $email = session('rydeen_verify_email');
        if (! $email) {
            return redirect()->route('dealer.login');
        }

        $result = $this->authService->verifyCode($email, $request->code);

        if (! $result['success']) {
            return back()->withErrors(['code' => $result['error']]);
        }

        auth('customer')->login($result['customer']);
        session()->forget('rydeen_verify_email');

        // Create device trust
        $uuid = $this->authService->createDeviceTrust($result['customer']);
        $cookie = cookie('rydeen_device', $uuid, config('rydeen.device_trust_days', 30) * 24 * 60);

        return redirect()->route('dealer.dashboard')->withCookie($cookie);
    }

    /**
     * Resend the verification code.
     */
    public function resendCode(Request $request)
    {
        $email = session('rydeen_verify_email');
        if (! $email) {
            return redirect()->route('dealer.login');
        }

        $result = $this->authService->generateCode($email);

        if (! $result['success']) {
            return back()->withErrors(['code' => $result['error']]);
        }

        return back()->with('status', trans('rydeen-auth::app.code-resent'));
    }

    /**
     * Log the customer out and redirect to login.
     */
    public function logout()
    {
        auth('customer')->logout();

        return redirect()->route('dealer.login');
    }
}
