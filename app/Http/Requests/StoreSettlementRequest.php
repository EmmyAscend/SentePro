<?php

namespace App\Http\Requests;

use App\Models\Settlement;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Settlement::class);
    }

    public function rules(): array
    {
        $rules = [
            'amount' => ['required', 'numeric', 'min:1'],
            'settlement_method_id' => ['required', 'exists:settlement_methods,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->user()->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'exists:businesses,id'];
        }

        return $rules;
    }
}
