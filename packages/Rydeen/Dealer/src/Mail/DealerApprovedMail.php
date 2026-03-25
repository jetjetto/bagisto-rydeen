<?php

namespace Rydeen\Dealer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DealerApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public $dealer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Rydeen Dealer Account Has Been Approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'rydeen-dealer::shop.emails.dealer-approved',
        );
    }
}
