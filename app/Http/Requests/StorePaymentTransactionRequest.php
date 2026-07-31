<?php

namespace App\Http\Requests;

use App\Models\PaymentTransaction;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', PaymentTransaction::class);
    }

    public function rules(): array
    {
        $rules = [
            'provider' => ['required', 'string', 'in:pesapal,yo_payments'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'status' => ['required', 'string', 'in:processing,completed,failed'],
            'external_reference' => ['required', 'string', 'max:255'],
        ];

        if ($this->user()->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'exists:businesses,id'];
        }

        return $rules;
    }
}
