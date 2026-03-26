<?php

namespace Rydeen\Dealer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderRepNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public $order,
        public $contact,
        public string $repName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dealer Order #' . ($this->order->increment_id ?? $this->order->id) . ' — New Order from Your Dealer',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'rydeen-dealer::shop.emails.order-rep-notification',
        );
    }
}
