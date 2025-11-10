<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePassTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create pass-types');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subdivision_id' => ['required', 'exists:subdivisions,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('pass_types', 'slug')
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'config' => ['nullable', 'array'],
            'config.required_fields' => ['nullable', 'array'],
            'config.optional_fields' => ['nullable', 'array'],
            'config.validation_rules' => ['nullable', 'array'],
            'default_validity_hours' => ['required', 'integer', 'min:1', 'max:8760'], // Max 1 year
            'max_validity_hours' => ['nullable', 'integer', 'min:1', 'gte:default_validity_hours'],
            'requires_approval' => ['required', 'boolean'],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'subdivision_id' => 'subdivision',
            'default_validity_hours' => 'default validity period',
            'max_validity_hours' => 'maximum validity period',
            'requires_approval' => 'approval requirement',
            'is_active' => 'status',
            'sort_order' => 'display order',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and dashes.',
            'slug.unique' => 'A pass type with this slug already exists.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #3B82F6).',
            'max_validity_hours.gte' => 'The maximum validity period must be greater than or equal to the default validity period.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from name if not provided
        if (!$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Set default color if not provided
        if (!$this->has('color')) {
            $this->merge([
                'color' => '#3B82F6',
            ]);
        }

        // Set default values
        if (!$this->has('requires_approval')) {
            $this->merge([
                'requires_approval' => false,
            ]);
        }

        if (!$this->has('is_active')) {
            $this->merge([
                'is_active' => true,
            ]);
        }

        if (!$this->has('sort_order')) {
            $this->merge([
                'sort_order' => 0,
            ]);
        }
    }
}
