<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardIssueReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('guard') ?? false;
    }

    public function rules(): array
    {
        $types = [
            'suspicious_activity',
            'unauthorized_entry',
            'equipment_issue',
            'medical_assistance',
            'other',
        ];

        return [
            'gate_id' => ['nullable', 'exists:gates,id'],
            'pass_code' => ['nullable', 'string', 'max:255'],
            'issue_type' => ['required', 'in:' . implode(',', $types)],
            'severity' => ['required', 'in:low,medium,high'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }
}
