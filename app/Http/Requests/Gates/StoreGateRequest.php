<?php

namespace App\Http\Requests\Gates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'subdivision_id' => ['required', 'exists:subdivisions,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:gates,code'],
            'location' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['entry', 'exit', 'both'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'notes' => ['nullable', 'string'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'coordinates.lng' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
