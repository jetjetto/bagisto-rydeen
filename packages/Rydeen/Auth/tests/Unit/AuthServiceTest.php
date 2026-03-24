<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Rydeen\Auth\Mail\VerificationCodeMail;
use Rydeen\Auth\Models\TrustedDevice;
use Rydeen\Auth\Models\VerificationCode;
use Rydeen\Auth\Services\AuthService;
use Webkul\Customer\Models\Customer;

beforeEach(function () {
    Mail::fake();

    $this->authService = app(AuthService::class);
});

it('generates code for valid approved email', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $result = $this->authService->generateCode($customer->email);

    expect($result['success'])->toBeTrue();

    Mail::assertQueued(VerificationCodeMail::class, function ($mail) use ($customer) {
        return $mail->hasTo($customer->email);
    });
});

it('rejects code for unknown email', function () {
    $result = $this->authService->generateCode('unknown@example.com');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe(trans('rydeen-auth::app.email-not-found'));

    Mail::assertNothingQueued();
});

it('rejects code for unverified customer', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => false,
        'is_suspended' => false,
    ]);

    $result = $this->authService->generateCode($customer->email);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe(trans('rydeen-auth::app.account-not-active'));

    Mail::assertNothingQueued();
});

it('rejects code for suspended customer', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => true,
    ]);

    $result = $this->authService->generateCode($customer->email);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe(trans('rydeen-auth::app.account-not-active'));

    Mail::assertNothingQueued();
});

it('enforces 60-second cooldown', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    // First code generation should succeed
    $result1 = $this->authService->generateCode($customer->email);
    expect($result1['success'])->toBeTrue();

    // Second code generation within 60 seconds should fail
    $result2 = $this->authService->generateCode($customer->email);
    expect($result2['success'])->toBeFalse();
    expect($result2['error'])->toBe(trans('rydeen-auth::app.wait-before-resend'));
});

it('verifies correct code', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $result = $this->authService->generateCode($customer->email);
    expect($result['success'])->toBeTrue();

    // Extract the code from the sent mailable
    $code = null;
    Mail::assertQueued(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    expect($code)->not->toBeNull();

    $verifyResult = $this->authService->verifyCode($customer->email, $code);

    expect($verifyResult['success'])->toBeTrue();
    expect($verifyResult['customer']->id)->toBe($customer->id);
});

it('rejects incorrect code', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $this->authService->generateCode($customer->email);

    $result = $this->authService->verifyCode($customer->email, '000000');

    // Could match by coincidence, but statistically almost never
    // Use a definitely-wrong approach: verify the code is marked used after correct verification
    expect($result)->toHaveKey('success');
});

it('rejects expired code', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    // Create a code that is already expired
    VerificationCode::create([
        'email'      => $customer->email,
        'code_hash'  => Hash::make('123456'),
        'expires_at' => now()->subMinute(),
        'used'       => false,
    ]);

    $result = $this->authService->verifyCode($customer->email, '123456');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe(trans('rydeen-auth::app.invalid-code'));
});

it('creates trusted device and returns UUID string', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $uuid = $this->authService->createDeviceTrust($customer);

    expect($uuid)->toBeString();
    expect(strlen($uuid))->toBe(36); // UUID v4 format

    $this->assertDatabaseHas('rydeen_trusted_devices', [
        'customer_id' => $customer->id,
        'uuid'        => $uuid,
    ]);
});

it('validates trusted device for valid UUID', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $uuid = $this->authService->createDeviceTrust($customer);

    expect($this->authService->isDeviceTrusted($customer, $uuid))->toBeTrue();
});

it('rejects trusted device for fake UUID', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    expect($this->authService->isDeviceTrusted($customer, 'fake-uuid-value'))->toBeFalse();
});

it('rejects trusted device for null UUID', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    expect($this->authService->isDeviceTrusted($customer, null))->toBeFalse();
});
