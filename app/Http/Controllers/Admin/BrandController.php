<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrandController
{
    public function index(): View
    {
        return view('admin.brands.index', [
            'brands' => Brand::query()->withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $brand = Brand::create($this->attributes($request->validated(), $request));

        return redirect()
            ->route('admin.brands.index')
            ->with('status', 'برند «'.$brand->name.'» اضافه شد.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', [
            'brand' => $brand,
        ]);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->attributes($request->validated(), $request));

        return redirect()
            ->route('admin.brands.index')
            ->with('status', 'برند به‌روزرسانی شد.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()
            ->route('admin.brands.index')
            ->with('status', 'برند حذف شد.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, Request $request): array
    {
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
