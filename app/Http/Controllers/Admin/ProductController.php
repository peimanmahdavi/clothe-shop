<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['images', 'category', 'brand'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['is_active' => true, 'rating' => 4.5, 'stock' => 0]),
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $product = Product::create($this->attributes($request, $data));

        $this->storeImages($product, $request->file('images', []));

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'محصول «'.$product->name.'» با موفقیت اضافه شد.');
    }

    public function edit(Product $product): View
    {
        $product->load('images');

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('sort_order')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request);

        if ($product->images()->count() + count($request->file('images', [])) > 3) {
            return back()
                ->withErrors(['images' => 'برای هر محصول حداکثر ۳ تصویر مجاز است. ابتدا یکی از تصاویر قبلی را حذف کنید.'])
                ->withInput();
        }

        $product->update($this->attributes($request, $data));

        $this->storeImages($product, $request->file('images', []));

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'تغییرات محصول «'.$product->name.'» ذخیره شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            Storage::disk('products')->delete($image->path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'محصول حذف شد.');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('products')->delete($image->path);
        $image->delete();

        return back()->with('status', 'تصویر محصول حذف شد.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1000', 'max:999999999'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'price.min' => 'قیمت باید حداقل ۱٬۰۰۰ تومان باشد.',
            'sale_price.lt' => 'قیمت با تخفیف باید کمتر از قیمت اصلی باشد.',
            'images.max' => 'حداکثر ۳ تصویر برای هر محصول مجاز است.',
            'images.*.mimes' => 'فرمت تصویر باید jpg، png یا webp باشد.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(Request $request, array $data): array
    {
        $data['brand_id'] = $data['brand_id'] ?? null;
        $data['sale_price'] = $data['sale_price'] ?? null;
        $data['rating'] = $data['rating'] ?? 4.5;
        $data['reviews_count'] = $data['reviews_count'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        unset($data['images']);

        return $data;
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        if ($images === []) {
            return;
        }

        $nextSortOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($images as $index => $image) {
            $product->images()->create([
                'path' => $image->store('', 'products'),
                'sort_order' => $nextSortOrder + $index,
            ]);
        }
    }
}
