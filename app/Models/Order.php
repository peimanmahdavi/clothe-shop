<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'number',
    'user_id',
    'product_id',
    'address_id',
    'customer_name',
    'shipping_first_name',
    'shipping_last_name',
    'phone',
    'shipping_phone',
    'total',
    'subtotal',
    'discount_code',
    'discount_amount',
    'status',
    'size',
    'color',
    'tracking_code',
    'placed_at',
])]
class Order extends Model
{
    /**
     * Status labels used across the admin panel.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        'pending_transaction' => 'در انتظار ثبت فیش',
        'pending_verification' => 'در انتظار تایید فیش',
        'pending' => 'در انتظار پرداخت',
        'processing' => 'در حال پردازش',
        'shipped' => 'ارسال شده',
        'completed' => 'تکمیل شده',
        'cancelled' => 'لغو شده',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'subtotal' => 'integer',
            'discount_amount' => 'integer',
            'placed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getShippingFullNameAttribute(): string
    {
        if ($this->shipping_first_name || $this->shipping_last_name) {
            return trim("{$this->shipping_first_name} {$this->shipping_last_name}");
        }

        return $this->customer_name;
    }

    public function getShippingPhoneAttribute(): string
    {
        return $this->shipping_phone ?? $this->phone;
    }

    /**
     * Orders that count towards revenue.
     *
     * @param  Builder<Order>  $query
     * @return Builder<Order>
     */
    public function scopeRevenue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelled');
    }
}
