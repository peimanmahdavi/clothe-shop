<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Discount;

/**
 * Resolves and applies discount codes against a cart subtotal.
 */
class DiscountService
{
    /**
     * Validate a code against the cart lines.
     *
     * @param  iterable<CartItem>  $items
     * @return array{discount: ?Discount, amount: int, message: string}
     */
    public function resolve(?string $code, iterable $items, int $subtotal): array
    {
        if ($code === null || trim($code) === '') {
            return ['discount' => null, 'amount' => 0, 'message' => ''];
        }

        $discount = Discount::findByCode($code);

        if (! $discount) {
            return ['discount' => null, 'amount' => 0, 'message' => 'کد تخفیف معتبر نیست یا منقضی شده است.'];
        }

        // Sum only the lines this discount actually applies to.
        $eligible = 0;
        foreach ($items as $item) {
            $product = $item->product;

            if ($product && $discount->appliesToProduct($product)) {
                $eligible += $item->unit_price * $item->quantity;
            }
        }

        if ($eligible <= 0) {
            return ['discount' => null, 'amount' => 0, 'message' => 'این کد تخفیف برای کالاهای سبد شما معتبر نیست.'];
        }

        $amount = (int) floor($eligible * ($discount->percentage / 100));
        $amount = min($amount, $subtotal);

        return ['discount' => $discount, 'amount' => $amount, 'message' => 'کد تخفیف اعمال شد.'];
    }
}
