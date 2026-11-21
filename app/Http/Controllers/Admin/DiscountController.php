<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreDiscountRequest;
use App\Http\Requests\Admin\UpdateDiscountRequest;
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

    public function store(StoreDiscountRequest $request): RedirectResponse
    {
        $discount = Discount::create($this->attributes($request->validated(), $request));

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

    public function update(UpdateDiscountRequest $request, Discount $discount): RedirectResponse
    {
        $discount->update($this->attributes($request->validated(), $request));

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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, Request $request): array
    {
        $data['is_active'] = $request->boolean('is_active');
        $data['product_ids'] = $data['applies_to'] === 'products' ? ($data['product_ids'] ?? []) : null;
        $data['brand_ids'] = $data['applies_to'] === 'brands' ? ($data['brand_ids'] ?? []) : null;
        $data['category_ids'] = $data['applies_to'] === 'categories' ? ($data['category_ids'] ?? []) : null;

        return $data;
    }
}
