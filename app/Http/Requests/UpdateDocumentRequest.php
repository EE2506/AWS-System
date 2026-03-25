<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'control_number' => ['required', 'string', 'max:8'],
            'recipient_name' => ['sometimes', 'required', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:50'],
            'recipient_address' => ['nullable', 'string', 'max:1000'],
            'document_date' => ['sometimes', 'required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],

            // Items validation
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required_with:items', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:500'],
        ];

        if ($this->user()->hasRole('admin')) {
            $rules['status'] = ['nullable', 'string', Rule::in([Document::STATUS_DRAFT, Document::STATUS_FINALIZED])];
        }

        return $rules;
    }
}
