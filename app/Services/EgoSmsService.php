<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * EgoSMS (egosms.co) is SentePro's own platform-wide SMS account, unlike the
 * per-business GatewayProvider credentials used for payment gateways — so
 * this reads a single account's credentials straight from config/services.php,
 * not a per-tenant database row.
 */
class EgoSmsService
{
    public function send(string $to, string $message): array
    {
        $response = Http::baseUrl(config('services.egosms.base_url'))
            ->acceptJson()
            ->post('/api/v1/json/', [
                'method' => 'SendSms',
                'userdata' => [
                    'username' => config('services.egosms.username'),
                    'password' => config('services.egosms.password'),
                ],
                'msgdata' => [
                    [
                        'number' => $to,
                        'message' => $message,
                        'senderid' => config('services.egosms.sender_id'),
                    ],
                ],
            ])
            ->throw();

        $data = $response->json();

        return [
            'status' => ($data['Status'] ?? null) === 'OK' ? 'sent' : 'failed',
            'raw' => $data,
        ];
    }
}
