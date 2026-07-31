<?php

namespace App\Http\Requests;

use App\Models\WebhookEvent;
use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WebhookEvent::class);
    }

    public function rules(): array
    {
        $rules = [
            'provider' => ['required', 'string', 'max:255'],
            'event' => ['required', 'string', 'max:255'],
            'payload' => ['required', 'json'],
        ];

        if ($this->user()->isSuperAdmin()) {
            $rules['business_id'] = ['required', 'exists:businesses,id'];
        }

        return $rules;
    }
}
