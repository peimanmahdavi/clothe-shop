<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lt:price', 'max:999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => [
                'image', 'mimes:jpg,jpeg,png,webp', 'max:5120',
                'dimensions:max_width=4096,max_height=4096',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.min' => 'قیمت باید حداقل ۱٬۰۰۰ تومان باشد.',
            'sale_price.lt' => 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.',
            'images.max' => 'حداکثر ۳ تصویر برای هر محصول مجاز است.',
            'images.*.mimes' => 'فرمت تصویر باید jpg، png یا webp باشد.',
            'images.*.max' => 'حجم هر تصویر حداکثر ۵ مگابایت باشد.',
        ];
    }
}
