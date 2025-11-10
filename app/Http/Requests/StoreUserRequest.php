<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'subdivision_ids' => ['nullable', 'array'],
            'subdivision_ids.*' => ['exists:subdivisions,id'],
            'primary_subdivision_id' => ['nullable', 'exists:subdivisions,id'],
            'gate_ids' => ['nullable', 'array'],
            'gate_ids.*' => ['exists:gates,id'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],
            'two_factor_enabled' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'phone' => 'phone number',
            'role' => 'user role',
            'subdivision_ids' => 'assigned subdivisions',
            'primary_subdivision_id' => 'primary subdivision',
            'gate_ids' => 'assigned gates',
            'two_factor_enabled' => '2FA status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert subdivision_ids and gate_ids to JSON if they are arrays
        if ($this->has('subdivision_ids') && is_array($this->subdivision_ids)) {
            $this->merge([
                'subdivision_ids' => json_encode($this->subdivision_ids),
            ]);
        }

        if ($this->has('gate_ids') && is_array($this->gate_ids)) {
            $this->merge([
                'gate_ids' => json_encode($this->gate_ids),
            ]);
        }
    }
}
