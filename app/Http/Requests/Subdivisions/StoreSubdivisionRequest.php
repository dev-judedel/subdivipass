<?php

namespace App\Http\Requests\Subdivisions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubdivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:subdivisions,code'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'notes' => ['nullable', 'string'],
            'settings' => ['nullable', 'array'],
            'settings.requires_approval' => ['nullable', 'boolean'],
            'settings.default_pass_validity_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
            'settings.allow_manual_entry' => ['nullable', 'boolean'],
            'settings.send_guard_alerts' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => 'subdivision code',
            'settings.default_pass_validity_hours' => 'default pass validity (hours)',
        ];
    }
}
