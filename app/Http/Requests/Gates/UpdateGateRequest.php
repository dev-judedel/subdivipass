<?php

namespace App\Http\Requests\Gates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function rules(): array
    {
        $gateId = $this->route('gate')?->id ?? null;

        return [
            'subdivision_id' => ['required', 'exists:subdivisions,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('gates', 'code')->ignore($gateId),
            ],
            'location' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['entry', 'exit', 'both'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'maintenance'])],
            'notes' => ['nullable', 'string'],
            'coordinates' => ['nullable', 'array'],
            'coordinates.lat' => ['nullable', 'numeric', 'between:-90,90'],
            'coordinates.lng' => ['nullable', 'numeric', 'between:-180,180'],
            'settings' => ['nullable', 'array'],
            'settings.requires_incident_report' => ['nullable', 'boolean'],
            'settings.auto_notify_admin' => ['nullable', 'boolean'],
            'settings.allow_manual_entry' => ['nullable', 'boolean'],
            'settings.enforce_device_lock' => ['nullable', 'boolean'],
            'settings.max_scan_per_minute' => ['nullable', 'integer', 'min:10', 'max:300'],
            'settings.guard_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}
