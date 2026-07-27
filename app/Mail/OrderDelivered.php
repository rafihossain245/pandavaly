<?php

namespace App\Mail;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SalesOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Order ' . $this->order->order_no . ' Has Been Delivered',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order_delivered');
    }
}
