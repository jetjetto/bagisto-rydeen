<?php

namespace Rydeen\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Rydeen\Auth\Mail\VerificationCodeMail;
use Rydeen\Auth\Models\TrustedDevice;
use Rydeen\Auth\Models\VerificationCode;
use Webkul\Customer\Repositories\CustomerRepository;

class AuthService
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    /**
     * Generate and send a 6-digit verification code to the given email.
     */
    public function generateCode(string $email): array
    {
        $customer = $this->customerRepository->findOneByField('email', $email);

        if (! $customer) {
            return ['success' => false, 'error' => trans('rydeen-auth::app.email-not-found')];
        }

        if (! $customer->is_verified || $customer->is_suspended) {
            return ['success' => false, 'error' => trans('rydeen-auth::app.account-not-active')];
        }

        // 60-second cooldown
        $recent = VerificationCode::where('email', $email)
            ->where('created_at', '>', now()->subSeconds(config('rydeen.code_resend_cooldown', 60)))
            ->exists();

        if ($recent) {
            return ['success' => false, 'error' => trans('rydeen-auth::app.wait-before-resend')];
        }

        // Rate limit
        $hourCount = VerificationCode::where('email', $email)
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($hourCount >= config('rydeen.code_max_per_hour', 5)) {
            return ['success' => false, 'error' => trans('rydeen-auth::app.too-many-requests')];
        }

        // Invalidate old codes
        VerificationCode::where('email', $email)->where('used', false)->update(['used' => true]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'email'      => $email,
            'code_hash'  => Hash::make($code),
            'expires_at' => now()->addMinutes(config('rydeen.code_expiry_minutes', 10)),
        ]);

        Mail::to($email)->send(new VerificationCodeMail($code));

        return ['success' => true];
    }

    /**
     * Verify a 6-digit code for the given email.
     */
    public function verifyCode(string $email, string $code): array
    {
        $records = VerificationCode::where('email', $email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($records as $record) {
            if (Hash::check($code, $record->code_hash)) {
                $record->update(['used' => true]);
                $customer = $this->customerRepository->findOneByField('email', $email);

                return ['success' => true, 'customer' => $customer];
            }
        }

        return ['success' => false, 'error' => trans('rydeen-auth::app.invalid-code')];
    }

    /**
     * Create a trusted device record and return the UUID.
     */
    public function createDeviceTrust($customer): string
    {
        $uuid = (string) Str::uuid();

        TrustedDevice::create([
            'customer_id' => $customer->id,
            'uuid'        => $uuid,
            'expires_at'  => now()->addDays(config('rydeen.device_trust_days', 30)),
        ]);

        return $uuid;
    }

    /**
     * Check if a device is trusted for the given customer.
     */
    public function isDeviceTrusted($customer, ?string $uuid): bool
    {
        if (! $uuid) {
            return false;
        }

        return TrustedDevice::where('customer_id', $customer->id)
            ->where('uuid', $uuid)
            ->where('expires_at', '>', now())
            ->exists();
    }
}
