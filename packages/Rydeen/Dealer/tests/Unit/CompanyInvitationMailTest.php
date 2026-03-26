<?php

use Rydeen\Dealer\Mail\CompanyInvitationMail;

it('builds with correct subject and view', function () {
    $dealer = (object) [
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
    ];

    $mailable = new CompanyInvitationMail($dealer, 'https://example.com/reset-password/abc123?email=john@example.com');

    $mailable->assertHasSubject('Welcome to the Rydeen Dealer Portal');
    $mailable->assertSeeInHtml('John');
    $mailable->assertSeeInHtml('https://example.com/reset-password/abc123?email=john@example.com');
    $mailable->assertSeeInHtml('RYDEEN');
    $mailable->assertSeeInHtml('Set Your Password');
});
