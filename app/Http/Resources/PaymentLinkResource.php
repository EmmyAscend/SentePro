<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'custom_amount' => $this->custom_amount,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'description' => $this->description,
            'status' => $this->status,
            'checkout_url' => route('checkout.show', $this->resource),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
