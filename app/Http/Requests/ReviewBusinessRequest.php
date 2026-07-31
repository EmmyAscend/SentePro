<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('business'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,under_review,approved,rejected,suspended'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
