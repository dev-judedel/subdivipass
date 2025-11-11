<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guard') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('method') && $this->routeIs('guard.pin.validate')) {
            $this->merge(['method' => 'pin']);
        }
    }

    public function rules(): array
    {
        return [
            'gate_id' => ['required', 'exists:gates,id'],
            'code' => ['required', 'string', 'max:1000'], // Increased to handle QR JSON data
            'method' => ['required', 'in:qr,pin,pass_number'],
            'scan_type' => ['nullable', 'in:entry,exit,validation'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'was_offline' => ['nullable', 'boolean'],
        ];
    }
}
