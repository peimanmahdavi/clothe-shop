<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'order_id',
    'user_id',
    'amount',
    'gateway',
    'payment_method',
    'ref_id',
    'tracking_code',
    'card_number',
    'receipt_image',
    'status',
    'description',
    'admin_note',
    'paid_at',
])]
class Transaction extends Model
{
    public const STATUSES = [
        'pending' => 'در انتظار',
        'paid' => 'موفق',
        'verified' => 'تأیید شده',
        'rejected' => 'رد شده',
        'failed' => 'ناموفق',
        'refunded' => 'مسترد شده',
    ];

    public const GATEWAYS = [
        'zarinpal' => 'زرین‌پال',
        'mellat' => 'ملت',
        'card' => 'کارت به کارت',
        'wallet' => 'کیف پول',
    ];

    public const PAYMENT_METHODS = [
        'gateway' => 'درگاه پرداخت',
        'card_to_card' => 'کارت به کارت',
    ];

    /**
     * Destination card for card-to-card transfers (configurable via env).
     */
    public static function destinationCard(): string
    {
        return config('payment.card_to_card.destination_card', '6037-9975-1234-5678');
    }

    public static function destinationAccountName(): string
    {
        return config('payment.card_to_card.account_name', 'آسمان‌پوش');
    }

    public static function destinationBankName(): string
    {
        return config('payment.card_to_card.bank_name', 'بانک ملت');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function gatewayLabel(): string
    {
        return self::GATEWAYS[$this->gateway] ?? $this->gateway;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public function isCardToCard(): bool
    {
        return $this->payment_method === 'card_to_card';
    }

    public function hasReceipt(): bool
    {
        return filled($this->receipt_image);
    }

    /**
     * Public URL for the uploaded receipt, or null when none exists.
     */
    public function receiptUrl(): ?string
    {
        return $this->hasReceipt() ? asset('storage/' . $this->receipt_image) : null;
    }

    /**
     * Mask a raw PAN to something like 6037-****-****-1234.
     */
    public static function maskCard(?string $pan): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $pan);

        if ($digits === null || strlen($digits) < 8) {
            return null;
        }

        $first = substr($digits, 0, 4);
        $last = substr($digits, -4);

        return $first.'-****-****-'.$last;
    }

    /**
     * Generate a unique tracking code (شماره پیگیری) for a transaction.
     */
    public static function generateTrackingCode(): string
    {
        do {
            $code = (string) random_int(10000000, 99999999);
        } while (static::query()->where('tracking_code', $code)->exists());

        return $code;
    }

    /**
     * Transactions that count as successful revenue.
     *
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }
}
