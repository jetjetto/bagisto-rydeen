<?php

namespace Rydeen\Dealer\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use SerializesModels;

    public function __construct(public $order, public $contact = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmation — #' . ($this->order->increment_id ?? $this->order->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'rydeen-dealer::shop.emails.order-confirmation',
        );
    }
}
