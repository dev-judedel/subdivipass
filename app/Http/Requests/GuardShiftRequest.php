<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guard') ?? false;
    }

    public function rules(): array
    {
        return [
            'gate_id' => ['nullable', 'exists:gates,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
