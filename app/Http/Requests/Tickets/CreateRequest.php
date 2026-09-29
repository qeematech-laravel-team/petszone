<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'vendor_id' => ['nullable', 'integer', 'exists:vendors,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:jpeg,png,jpg,gif,pdf,doc,docx', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject.required' => __('The subject is required.'),
            'subject.max' => __('The subject must not exceed 255 characters.'),
            'description.required' => __('The description is required.'),
            'vendor_id.exists' => __('The selected vendor does not exist.'),
            'attachments.array' => __('Attachments must be an array.'),
            'attachments.*.file' => __('Each attachment must be a file.'),
            'attachments.*.mimes' => __('Each attachment must be a file of type: jpeg, png, jpg, gif, pdf, doc, docx.'),
            'attachments.*.max' => __('Each attachment must not be larger than 5MB.'),
        ];
    }
}
