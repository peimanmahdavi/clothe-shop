<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Privileged columns (is_admin, is_active, otp_*) are intentionally absent:
// they must never be settable through a public registration/profile form.
#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'first_name',
    'last_name',
])]
#[Hidden(['password', 'remember_token', 'otp_code', 'otp_expires_at', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('is_admin', true);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function roleLabel(): string
    {
        return $this->is_admin ? 'مدیر' : 'کاربر عادی';
    }

    public function statusLabel(): string
    {
        return $this->is_active ? 'فعال' : 'مسدود';
    }

    public function isLastActiveAdmin(): bool
    {
        return $this->is_admin && $this->is_active
            && static::query()->admins()->active()->count() === 1;
    }

    /**
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasOne<Cart, $this>
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function getDefaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->name;
    }

    /**
     * Generate a new API Bearer token for this user.
     * Only the hash is stored; the plain token is returned to the client.
     */
    public function generateApiToken(): string
    {
        $plain = bin2hex(random_bytes(32));
        $this->api_token = hash('sha256', $plain);
        $this->save();

        return $plain;
    }

    /**
     * Revoke the current API token (logout).
     */
    public function revokeApiToken(): void
    {
        $this->api_token = null;
        $this->save();
    }

    public function generateOtp(): string
    {
        // When debugging is enabled, always use 123456 for everyone.
        if (config('app.debug')) {
            $this->otp_code = '123456';
        } else {
            $this->otp_code = (string) random_int(100000, 999999);
        }
        $this->otp_expires_at = now()->addMinutes(5);
        $this->save();

        return $this->otp_code;
    }

    public function verifyOtp(string $otp): bool
    {
        if (! $this->otp_code || ! $this->otp_expires_at) {
            return false;
        }

        if (now()->gt($this->otp_expires_at)) {
            return false;
        }

        if ($this->otp_code !== $otp) {
            return false;
        }

        // Clear OTP after successful verification
        $this->otp_code = null;
        $this->otp_expires_at = null;
        $this->save();

        return true;
    }

    public function hasValidOtp(): bool
    {
        return $this->otp_code !== null
            && $this->otp_expires_at !== null
            && now()->lt($this->otp_expires_at);
    }
}
