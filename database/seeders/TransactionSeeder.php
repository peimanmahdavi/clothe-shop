<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()->orderBy('id')->get();

        if ($orders->isEmpty()) {
            return;
        }

        $gateways = ['zarinpal', 'zarinpal', 'mellat', 'card', 'wallet'];
        $cards = ['6037991234567890', '6104337654321098', '6274129876543210', '5022291234567890'];
        $paidStatuses = ['completed', 'processing', 'shipped'];

        foreach ($orders as $index => $order) {
            // Every order gets its primary payment attempt.
            $isPending = $order->status === 'pending';
            $isCancelled = $order->status === 'cancelled';

            $status = match (true) {
                $isPending => 'pending',
                $isCancelled => 'failed',
                default => 'paid',
            };

            $paidAt = in_array($status, ['paid'], true)
                ? $order->placed_at->copy()->addMinutes(3 + ($index % 9))
                : null;

            Transaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $order->total,
                'gateway' => $gateways[$index % count($gateways)],
                'ref_id' => $status === 'paid' ? 'REF'.str_pad((string) (100000 + $index), 6, '0', STR_PAD_LEFT) : null,
                'tracking_code' => Transaction::generateTrackingCode(),
                'card_number' => in_array($gateways[$index % count($gateways)], ['zarinpal', 'mellat', 'card'], true)
                    ? Transaction::maskCard($cards[$index % count($cards)])
                    : null,
                'status' => $status,
                'description' => match ($status) {
                    'paid' => 'پرداخت با موفقیت انجام شد.',
                    'pending' => 'در انتظار پرداخت توسط کاربر.',
                    'failed' => 'پرداخت ناموفق یا لغو شده است.',
                    default => null,
                },
                'paid_at' => $paidAt,
                'created_at' => $order->placed_at,
                'updated_at' => $order->placed_at,
            ]);

            // Occasionally a failed first attempt followed by a successful retry.
            if ($status === 'paid' && $index % 6 === 0) {
                Transaction::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'gateway' => $gateways[$index % count($gateways)],
                    'ref_id' => null,
                    'tracking_code' => Transaction::generateTrackingCode(),
                    'card_number' => null,
                    'status' => 'failed',
                    'description' => 'تلاش ناموفق اولیه؛ تراکنش مجدداً انجام شد.',
                    'paid_at' => null,
                    'created_at' => $order->placed_at->copy()->subMinutes(5),
                    'updated_at' => $order->placed_at->copy()->subMinutes(5),
                ]);
            }

            // A few refunded transactions on completed orders.
            if ($order->status === 'completed' && $index % 8 === 0) {
                Transaction::create([
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'gateway' => $gateways[$index % count($gateways)],
                    'ref_id' => 'RFD'.str_pad((string) (500000 + $index), 6, '0', STR_PAD_LEFT),
                    'tracking_code' => Transaction::generateTrackingCode(),
                    'card_number' => null,
                    'status' => 'refunded',
                    'description' => 'مبلغ به حساب مشتری بازگردانده شد.',
                    'paid_at' => $order->placed_at->copy()->addDays(2),
                    'created_at' => $order->placed_at->copy()->addDays(2),
                    'updated_at' => $order->placed_at->copy()->addDays(2),
                ]);
            }
        }
    }
}
