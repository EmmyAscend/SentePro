<?php

namespace App\Http\Requests;

use App\Models\WalletTransfer;
use Illuminate\Foundation\Http\FormRequest;

class StoreWalletTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WalletTransfer::class);
    }

    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
