<?php

namespace App\Http\Requests;

use App\Models\PaymentLink;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PaymentLink::class);
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'custom_amount' => ['required', 'boolean'],
            'expiry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->user()->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'exists:businesses,id'];
        }

        return $rules;
    }
}
