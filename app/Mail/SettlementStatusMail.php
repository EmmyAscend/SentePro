<?php

namespace App\Mail;

use App\Models\Settlement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SettlementStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Settlement $settlement, public readonly string $event) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'completed' => 'Your settlement has been completed',
            'rejected' => 'Your settlement request was rejected',
            'reversed' => 'Your settlement has been reversed',
            default => 'Your settlement is being retried',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.settlement-status',
        );
    }
}
