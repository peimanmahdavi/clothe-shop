<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Show the product catalog with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['images', 'category', 'brand']);

        // Search by name / description
        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->query('category')) {
            $query->where('category_id', $categoryId);
        }

        // Brand filter
        if ($brandId = $request->query('brand')) {
            $query->where('brand_id', $brandId);
        }

        // Price range
        if ($min = $request->query('min_price')) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [(int) $min]);
        }
        if ($max = $request->query('max_price')) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [(int) $max]);
        }

        // Availability
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        match ($request->query('sort')) {
            'price_asc' => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_desc' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'popular' => $query->orderByDesc('reviews_count'),
            'rating' => $query->orderByDesc('rating'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $brands = Brand::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => [
                'q' => $request->query('q'),
                'category' => $request->query('category'),
                'brand' => $request->query('brand'),
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'in_stock' => $request->boolean('in_stock'),
                'sort' => $request->query('sort', 'newest'),
            ],
        ]);
    }

    /**
     * Show a single product.
     */
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category', 'brand']);

        $related = Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['images', 'brand', 'category'])
            ->take(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'related' => $related,
        ]);
    }
}