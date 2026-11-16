<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscountController
{
    public function index(): View
    {
        return view('admin.discounts.index', $this->formData([
            'discounts' => Discount::query()->latest('created_at')->get(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $discount = Discount::create($this->attributes($request));

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', 'جشنواره «'.$discount->title.'» ایجاد شد.');
    }

    public function edit(Discount $discount): View
    {
        return view('admin.discounts.edit', $this->formData([
            'discount' => $discount,
        ]));
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $discount->update($this->attributes($request));

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', 'جشنواره به‌روزرسانی شد.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->delete();

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', 'جشنواره حذف شد.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function formData(array $data): array
    {
        return $data + [
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('sort_order')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:30'],
            'percentage' => ['required', 'integer', 'min:1', 'max:90'],
            'applies_to' => ['required', 'in:all,products,brands,categories'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', 'exists:brands,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'ends_at.after_or_equal' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['product_ids'] = $data['applies_to'] === 'products' ? ($data['product_ids'] ?? []) : null;
        $data['brand_ids'] = $data['applies_to'] === 'brands' ? ($data['brand_ids'] ?? []) : null;
        $data['category_ids'] = $data['applies_to'] === 'categories' ? ($data['category_ids'] ?? []) : null;

        return $data;
    }
}