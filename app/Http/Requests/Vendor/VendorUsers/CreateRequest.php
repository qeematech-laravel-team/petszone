<?php

namespace App\Http\Requests\Vendor\VendorUsers;

use App\Services\VendorUserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return vendorCan('create-vendor-users') || vendorCan('manage-vendor-users');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $assignable = app(VendorUserService::class)
            ->assignablePermissions($this->user())
            ->pluck('name')
            ->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'user_type' => ['required', 'string', Rule::in(['owner', 'branch'])],
            'branch_id' => [
                'nullable',
                'required_if:user_type,branch',
                'exists:branches,id',
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($assignable)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The name field is required.'),
            'name.max' => __('The name must not exceed 255 characters.'),
            'email.email' => __('The email must be a valid email address.'),
            'email.unique' => __('The email has already been taken.'),
            'phone.unique' => __('The phone number has already been taken.'),
            'password.required' => __('The password field is required.'),
            'password.min' => __('The password must be at least 8 characters.'),
            'password.confirmed' => __('The password confirmation does not match.'),
            'permissions.*.in' => __('One or more selected permissions are invalid.'),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->email && ! $this->phone) {
                $validator->errors()->add('email', __('You must provide at least an email or phone number.'));
            }

            if ($this->user_type === 'branch' && $this->branch_id) {
                $vendor = $this->user()->vendor();
                if ($vendor) {
                    $branchExists = \App\Models\Branch::where('id', $this->branch_id)
                        ->where('vendor_id', $vendor->id)
                        ->exists();

                    if (! $branchExists) {
                        $validator->errors()->add('branch_id', __('The selected branch does not belong to your vendor account.'));
                    }
                }
            }
        });
    }
}
