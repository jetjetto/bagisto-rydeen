<?php

namespace Rydeen\Dealer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Dealer Order #' . ($this->order->increment_id ?? $this->order->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'rydeen-dealer::shop.emails.order-submitted',
        );
    }
}
