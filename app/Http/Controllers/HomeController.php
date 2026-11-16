<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController
{
    public function __invoke(): View
    {
        $base = fn () => Product::query()
            ->where('is_active', true)
            ->with(['images', 'category', 'brand']);

        $featured = $base()
            ->orderByDesc('is_featured')
            ->orderByDesc('reviews_count')
            ->take(8)
            ->get();

        $bestSellers = $base()
            ->orderByDesc('reviews_count')
            ->take(8)
            ->get();

        $discounted = $base()
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->orderByDesc('reviews_count')
            ->take(6)
            ->get();

        $newest = $base()
            ->latest()
            ->take(6)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        $activeDiscount = Discount::query()->running()->orderBy('ends_at')->first();

        $heroProduct = $featured->firstWhere('is_featured', true) ?? $featured->first();

        return view('home', [
            'products' => $featured,
            'bestSellers' => $bestSellers,
            'discounted' => $discounted,
            'newest' => $newest,
            'categories' => $categories,
            'activeDiscount' => $activeDiscount,
            'heroProduct' => $heroProduct,
        ]);
    }
}