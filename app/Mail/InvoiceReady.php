<?php

namespace App\Mail;

use App\Models\SalesOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SalesOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Ready – Order ' . $this->order->order_no,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice_ready');
    }
}
