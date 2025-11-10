<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Users can update their own profile or if they have permission
        return $this->user()->id === $this->route('user')->id
            || $this->user()->can('edit users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'exists:roles,name'],
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

}
