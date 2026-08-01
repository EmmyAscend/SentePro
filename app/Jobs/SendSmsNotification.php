<?php

namespace App\Jobs;

use App\Services\EgoSmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly string $to, public readonly string $message) {}

    /**
     * SMS delivery is a best-effort side channel, never part of the
     * money-movement transaction that triggered it — every failure is caught
     * and logged here rather than allowed to propagate, so a bad EgoSMS
     * response or a network error can never unwind a payment credit or
     * refund. This also matters under the "sync" queue connection (used in
     * tests), where an uncaught exception would otherwise be re-thrown
     * straight back into the caller.
     */
    public function handle(EgoSmsService $sms): void
    {
        try {
            $result = $sms->send($this->to, $this->message);

            if ($result['status'] !== 'sent') {
                Log::warning('EgoSMS delivery failed', ['to' => $this->to, 'response' => $result['raw']]);
            }
        } catch (Throwable $e) {
            Log::error('EgoSMS request failed', ['to' => $this->to, 'error' => $e->getMessage()]);
        }
    }
}
