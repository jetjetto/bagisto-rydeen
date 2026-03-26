<?php

namespace Rydeen\Dealer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCustomerNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public $order,
        public $contact,
        public string $dealerName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Submitted on Your Behalf — #' . ($this->order->increment_id ?? $this->order->id),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'rydeen-dealer::shop.emails.order-customer-notification',
        );
    }
}
