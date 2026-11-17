<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'code', 'percentage', 'applies_to', 'product_ids', 'brand_ids', 'category_ids', 'description', 'starts_at', 'ends_at', 'is_active'])]
class Discount extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percentage' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'product_ids' => 'array',
            'brand_ids' => 'array',
            'category_ids' => 'array',
        ];
    }

    /**
     * Campaigns that are enabled and inside their date window.
     *
     * @param  Builder<Discount>  $query
     * @return Builder<Discount>
     */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /**
     * Resolve an active, in-window discount by its code (case-insensitive).
     */
    public static function findByCode(?string $code): ?self
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        return static::query()
            ->running()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])
            ->first();
    }

    public function isExpired(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    /**
     * Whether this discount applies to the given product.
     */
    public function appliesToProduct(Product $product): bool
    {
        return match ($this->applies_to) {
            'products' => in_array($product->id, $this->product_ids ?? [], true),
            'brands' => in_array($product->brand_id, $this->brand_ids ?? [], true),
            'categories' => in_array($product->category_id, $this->category_ids ?? [], true),
            default => true,
        };
    }

    public function scopeLabel(): string
    {
        return match ($this->applies_to) {
            'products' => 'محصولات انتخابی',
            'brands' => 'برندهای انتخابی',
            'categories' => 'دسته‌بندی‌های انتخابی',
            default => 'همه محصولات',
        };
    }
}
