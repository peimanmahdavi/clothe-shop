<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:30'],
            'percentage' => ['required', 'integer', 'min:1', 'max:90'],
            'applies_to' => ['required', 'in:all,products,brands,categories'],
            'product_ids' => ['nullable', 'array', 'max:1000'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'brand_ids' => ['nullable', 'array', 'max:1000'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
            'category_ids' => ['nullable', 'array', 'max:1000'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ends_at.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
        ];
    }
}
