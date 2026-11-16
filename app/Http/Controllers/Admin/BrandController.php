<?php

namespace App\Http\Controllers\Admin;

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

    public function store(Request $request): RedirectResponse
    {
        $brand = Brand::create($this->attributes($request));

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

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $brand->update($this->attributes($request));

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
     * @return array<string, mixed>
     */
    private function attributes(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
