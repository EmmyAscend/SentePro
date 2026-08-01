<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'available_balance' => (float) $this->available_balance,
            'pending_balance' => (float) $this->pending_balance,
            'reserved_balance' => (float) $this->reserved_balance,
            'settlement_balance' => (float) $this->settlement_balance,
        ];
    }
}
