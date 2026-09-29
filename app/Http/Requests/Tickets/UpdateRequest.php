<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(['pending', 'resolved', 'closed'])],
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
            'status.in' => __('The status must be pending, resolved, or closed.'),
            'attachments.array' => __('Attachments must be an array.'),
            'attachments.*.file' => __('Each attachment must be a file.'),
            'attachments.*.mimes' => __('Each attachment must be a file of type: jpeg, png, jpg, gif, pdf, doc, docx.'),
            'attachments.*.max' => __('Each attachment must not be larger than 5MB.'),
        ];
    }
}
