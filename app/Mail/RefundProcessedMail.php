<?php

namespace App\Mail;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundProcessedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Refund $refund) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your payment to '.$this->refund->business->business_name.' has been refunded',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.refund-processed',
        );
    }
}
