<?php

namespace Rydeen\Dealer\Listeners;

use Illuminate\Support\Facades\Mail;
use Rydeen\Dealer\Mail\CompanyInvitationMail;

class CompanyInvitationListener
{
    public function afterCreated($customer): void
    {
        if ($customer->type !== 'company') {
            return;
        }

        $loginUrl = route('dealer.login');

        try {
            Mail::to($customer->email)->send(new CompanyInvitationMail($customer, $loginUrl));

            session()->flash('info', "Onboarding email sent to {$customer->email}");
        } catch (\Exception $e) {
            report($e);

            session()->flash('warning', 'Company created but onboarding email failed — use Resend Invitation from the list.');
        }
    }
}
