<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create passes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'subdivision_id' => ['required', 'exists:subdivisions,id'],
            'pass_type_id' => ['required', 'exists:pass_types,id'],
            'pass_mode' => ['required', 'in:single,group'],
            'purpose' => ['required', 'string', 'max:500'],
            'destination' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'valid_from' => ['nullable', 'date', 'after_or_equal:today'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
            'metadata' => ['nullable', 'array'],
        ];

        // Single pass validation
        if ($this->input('pass_mode') === 'single') {
            $rules['visitor_name'] = ['required', 'string', 'max:255'];
            $rules['visitor_contact'] = ['nullable', 'string', 'max:255'];
            $rules['visitor_email'] = ['nullable', 'email', 'max:255'];
            $rules['visitor_company'] = ['nullable', 'string', 'max:255'];
            $rules['vehicle_plate'] = ['nullable', 'string', 'max:50'];
            $rules['vehicle_model'] = ['nullable', 'string', 'max:100'];
        }

        // Worker/Group pass validation
        if ($this->input('pass_mode') === 'group') {
            $rules['workers'] = ['required', 'array', 'min:1', 'max:50'];
            $rules['workers.*.worker_name'] = ['required', 'string', 'max:255'];
            $rules['workers.*.worker_contact'] = ['nullable', 'string', 'max:255'];
            $rules['workers.*.worker_email'] = ['nullable', 'email', 'max:255'];
            $rules['workers.*.worker_position'] = ['nullable', 'string', 'max:255'];
            $rules['workers.*.worker_id_number'] = ['nullable', 'string', 'max:100'];
            $rules['workers.*.photo'] = ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120']; // 5MB max
        }

        return $rules;
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'subdivision_id' => 'subdivision',
            'pass_type_id' => 'pass type',
            'pass_mode' => 'pass mode',
            'visitor_name' => 'visitor name',
            'visitor_contact' => 'contact number',
            'visitor_email' => 'email address',
            'visitor_company' => 'company name',
            'vehicle_plate' => 'vehicle plate number',
            'vehicle_model' => 'vehicle model',
            'valid_from' => 'valid from date',
            'valid_to' => 'valid to date',
            'workers' => 'workers list',
            'workers.*.worker_name' => 'worker name',
            'workers.*.worker_contact' => 'worker contact',
            'workers.*.worker_email' => 'worker email',
            'workers.*.worker_position' => 'worker position',
            'workers.*.worker_id_number' => 'worker ID number',
            'workers.*.photo' => 'worker photo',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'workers.required' => 'Please add at least one worker for the worker pass.',
            'workers.min' => 'Worker pass must have at least one worker.',
            'workers.max' => 'Worker pass cannot have more than 50 workers.',
            'workers.*.worker_name.required' => 'Worker name is required for all workers.',
            'workers.*.photo.image' => 'Worker photo must be an image file.',
            'workers.*.photo.mimes' => 'Worker photo must be a JPEG or PNG file.',
            'workers.*.photo.max' => 'Worker photo must be less than 5MB.',
        ];
    }
}
