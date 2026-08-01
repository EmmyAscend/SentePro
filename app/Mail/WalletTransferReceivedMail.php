<?php

namespace App\Mail;

use App\Models\WalletTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WalletTransferReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly WalletTransfer $walletTransfer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'ve received a wallet transfer on SentePro',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.wallet-transfer-received',
        );
    }
}
