<?php

namespace App\Services;

use App\Mail\WalletTransferReceivedMail;
use App\Models\Business;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WalletTransferService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * Resolves a Business ID, email, or phone number to exactly one business.
     * Neither email nor phone is unique on the businesses table, so an
     * ambiguous match is rejected rather than guessing which business was meant.
     */
    public function resolveRecipient(string $identifier): Business
    {
        $query = Business::query()->where('email', $identifier)->orWhere('phone', $identifier);

        if (ctype_digit($identifier)) {
            $query->orWhere('id', $identifier);
        }

        $matches = $query->limit(2)->get();

        if ($matches->isEmpty()) {
            throw ValidationException::withMessages([
                'recipient' => 'No business matches that Business ID, email, or phone number.',
            ]);
        }

        if ($matches->count() > 1) {
            throw ValidationException::withMessages([
                'recipient' => 'More than one business matches that email or phone number — use the Business ID instead.',
            ]);
        }

        return $matches->first();
    }

    /**
     * Moves money directly between two businesses' wallets — no gateway, no
     * fee, no pending state, per prompt.txt's Wallet Transfers spec.
     */
    public function transfer(Business $sender, Business $recipient, float $amount, ?string $note, User $initiatedBy): WalletTransfer
    {
        $walletTransfer = DB::transaction(function () use ($sender, $recipient, $amount, $note, $initiatedBy) {
            if ($sender->id === $recipient->id) {
                throw ValidationException::withMessages([
                    'recipient' => 'You cannot transfer to your own business.',
                ]);
            }

            if ($recipient->status !== 'approved') {
                throw ValidationException::withMessages([
                    'recipient' => 'The recipient business is not active.',
                ]);
            }

            // Lock both wallets in a fixed order (lower business id first) to
            // avoid a classic two-party deadlock between simultaneous transfers
            // moving in opposite directions. Wallet is BelongsToTenant, and the
            // sender/recipient are two different tenants in the same request —
            // the global scope would silently filter out whichever wallet isn't
            // the authenticated user's own business, so it's bypassed here and
            // the business_id match is done explicitly instead.
            [$firstBusinessId, $secondBusinessId] = $sender->id < $recipient->id
                ? [$sender->id, $recipient->id]
                : [$recipient->id, $sender->id];

            $firstWallet = Wallet::withoutGlobalScope(TenantScope::class)
                ->where('business_id', $firstBusinessId)->lockForUpdate()->first();
            $secondWallet = Wallet::withoutGlobalScope(TenantScope::class)
                ->where('business_id', $secondBusinessId)->lockForUpdate()->first();

            $senderWallet = $firstBusinessId === $sender->id ? $firstWallet : $secondWallet;
            $recipientWallet = $firstBusinessId === $recipient->id ? $firstWallet : $secondWallet;

            if ($amount > (float) $senderWallet->available_balance) {
                throw ValidationException::withMessages([
                    'amount' => 'The transfer amount exceeds the available wallet balance.',
                ]);
            }

            $senderWallet->update(['available_balance' => $senderWallet->available_balance - $amount]);
            $recipientWallet->update(['available_balance' => $recipientWallet->available_balance + $amount]);

            $walletTransfer = WalletTransfer::create([
                'sender_business_id' => $sender->id,
                'recipient_business_id' => $recipient->id,
                'initiated_by' => $initiatedBy->id,
                'reference' => 'WTXN-'.Str::upper(Str::random(10)),
                'amount' => $amount,
                'note' => $note,
            ]);

            $this->auditLog->record('wallet.transferred', $walletTransfer, [
                'amount' => (string) $amount,
                'recipient_business_id' => $recipient->id,
            ]);

            return $walletTransfer;
        });

        $recipients = $recipient->admins->pluck('email');

        if ($recipients->isNotEmpty()) {
            Mail::to($recipients)->queue(new WalletTransferReceivedMail($walletTransfer));
        }

        return $walletTransfer;
    }
}
