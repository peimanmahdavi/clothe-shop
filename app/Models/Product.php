<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'brand_id',
    'name',
    'description',
    'price',
    'sale_price',
    'stock',
    'sizes',
    'colors',
    'rating',
    'reviews_count',
    'is_featured',
    'is_active',
])]
class Product extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'sizes' => 'array',
            'colors' => 'array',
            'rating' => 'decimal:1',
            'reviews_count' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function finalPrice(): int
    {
        return $this->sale_price ?? $this->price;
    }

    public function discountPercent(): int
    {
        if ($this->sale_price === null || $this->price <= 0 || $this->sale_price >= $this->price) {
            return 0;
        }

        return (int) round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    public function galleryImages(): \Illuminate\Support\Collection
    {
        return $this->images->take(3)->values();
    }

    public function defaultImageIndex(): int
    {
        return (int) floor(($this->galleryImages()->count() - 1) / 2);
    }
}
