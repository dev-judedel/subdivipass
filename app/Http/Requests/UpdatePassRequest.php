<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit passes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visitor_name' => ['sometimes', 'required', 'string', 'max:255'],
            'visitor_contact' => ['nullable', 'string', 'max:255'],
            'visitor_email' => ['nullable', 'email', 'max:255'],
            'visitor_company' => ['nullable', 'string', 'max:255'],
            'vehicle_plate' => ['nullable', 'string', 'max:50'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'purpose' => ['sometimes', 'required', 'string', 'max:500'],
            'destination' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'visitor_name' => 'visitor name',
            'visitor_contact' => 'contact number',
            'visitor_email' => 'email address',
            'visitor_company' => 'company name',
            'vehicle_plate' => 'vehicle plate number',
            'vehicle_model' => 'vehicle model',
            'valid_from' => 'valid from date',
            'valid_to' => 'valid to date',
        ];
    }
}
