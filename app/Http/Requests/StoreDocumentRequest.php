<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:carte_grise,assurance,controle_technique',
            'expiration_date' => 'required|date|after:today',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Document type is required.',
            'type.in' => 'Document type must be one of: carte_grise, assurance, controle_technique.',
            'expiration_date.required' => 'Expiration date is required.',
            'expiration_date.date' => 'Expiration date must be a valid date.',
            'expiration_date.after' => 'Expiration date must be in the future.',
            'file.file' => 'The uploaded file must be a valid file.',
            'file.mimes' => 'File must be a PDF, JPG, JPEG, or PNG.',
            'file.max' => 'File size must not exceed 2MB.',
        ];
    }
}
