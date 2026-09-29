<?php

namespace App\Http\Requests\Admin\Customers;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return adminCan('manage-admin-customers');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\User|null $customer */
        $customer = $this->route('user');
        $customerId = $customer?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($customerId),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(User::class)->ignore($customerId),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.unique' => __('The email has already been taken.'),
            'phone.unique' => __('The phone has already been taken.'),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->email && ! $this->phone) {
                $validator->errors()->add('email', __('You must provide at least an email or phone number.'));
            }
        });
    }
}
