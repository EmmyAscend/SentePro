<?php

namespace App\Http\Requests;

use App\Models\Announcement;
use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', Announcement::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
