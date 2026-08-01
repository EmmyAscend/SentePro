<?php

namespace App\Mail;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DisputeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Dispute $dispute, public readonly string $event) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->event) {
            'resolved' => 'Dispute resolved: '.$this->dispute->reason,
            'rejected' => 'Dispute rejected: '.$this->dispute->reason,
            default => 'New dispute raised: '.$this->dispute->reason,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dispute',
        );
    }
}
