<?php

use Illuminate\Support\Facades\Mail;
use Rydeen\Auth\Mail\VerificationCodeMail;
use Webkul\Customer\Models\Customer;

beforeEach(function () {
    Mail::fake();
});

it('shows the login page', function () {
    $response = $this->get(route('dealer.login'));

    $response->assertStatus(200);
    $response->assertSee(trans('rydeen-auth::app.login-title'));
});

it('sends code and redirects to verify for valid email', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $response = $this->post(route('dealer.login.send-code'), [
        'email' => $customer->email,
    ]);

    $response->assertRedirect(route('dealer.verify.form'));

    Mail::assertQueued(VerificationCodeMail::class, function ($mail) use ($customer) {
        return $mail->hasTo($customer->email);
    });
});

it('rejects unknown email with error', function () {
    $response = $this->post(route('dealer.login.send-code'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

it('redirects to login when accessing verify without session email', function () {
    $response = $this->get(route('dealer.verify.form'));

    $response->assertRedirect(route('dealer.login'));
});

it('verifies valid code and redirects to dashboard with cookie', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    // Send code
    $this->post(route('dealer.login.send-code'), [
        'email' => $customer->email,
    ]);

    // Extract code from sent mail
    $code = null;
    Mail::assertQueued(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$code) {
        $code = $mail->code;

        return true;
    });

    expect($code)->not->toBeNull();

    // Verify code
    $response = $this->withSession(['rydeen_verify_email' => $customer->email])
        ->post(route('dealer.verify'), [
            'code' => $code,
        ]);

    $response->assertRedirect(route('dealer.dashboard'));
    $response->assertCookie('rydeen_device');
});

it('logs out and redirects to login', function () {
    $customer = Customer::factory()->create([
        'is_verified'  => true,
        'is_suspended' => false,
    ]);

    $this->actingAs($customer, 'customer');

    $response = $this->post(route('dealer.logout'));

    $response->assertRedirect(route('dealer.login'));
});
