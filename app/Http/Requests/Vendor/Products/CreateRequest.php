<?php

namespace App\Http\Requests\Vendor\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only vendor owners or vendor users with user_type 'owner' can create products
        // Branch users cannot create products
        return canCreateProducts();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['simple', 'variable'])],
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.ar' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.ar' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'price' => ['required_if:type,simple', 'nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'string', Rule::in(['percentage', 'fixed'])],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_new' => ['nullable', 'boolean'],
            'is_bookable' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:5120'],
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
            'type.required' => __('The product type is required.'),
            'type.in' => __('The product type must be either simple or variable.'),
            'name.required' => __('The product name is required.'),
            'name.array' => __('The product name must be an array for translations.'),
            'name.en.required' => __('The English name is required.'),
            'name.ar.required' => __('The Arabic name is required.'),
            'name.*.string' => __('Each translation must be a string.'),
            'name.*.max' => __('Each translation must not exceed 255 characters.'),
            'description.array' => __('The description must be an array for translations.'),
            'description.*.string' => __('Each description translation must be a string.'),
            'thumbnail.image' => __('The thumbnail must be an image.'),
            'thumbnail.mimes' => __('The thumbnail must be a file of type: jpeg, png, jpg, gif, svg, webp.'),
            'thumbnail.max' => __('The thumbnail must not be larger than 5MB.'),
            'sku.unique' => __('This SKU is already taken.'),
            'slug.unique' => __('This slug is already taken.'),
            'price.required_if' => __('The price is required for simple products.'),
            'price.numeric' => __('The price must be a number.'),
            'price.min' => __('The price must be at least 0.'),
            'discount.numeric' => __('The discount must be a number.'),
            'discount.min' => __('The discount must be at least 0.'),
            'discount_type.in' => __('The discount type must be either percentage or fixed.'),
            'categories.array' => __('Categories must be an array.'),
            'categories.*.exists' => __('One or more selected categories do not exist.'),
            'images.array' => __('Images must be an array.'),
            'images.*.image' => __('Each file must be an image.'),
            'images.*.mimes' => __('Each image must be a file of type: jpeg, png, jpg, gif, svg, webp.'),
            'images.*.max' => __('Each image must not be larger than 5MB.'),
        ];
    }
}
