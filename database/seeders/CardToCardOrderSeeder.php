<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CardToCardOrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('is_admin', false)->orderBy('id')->take(3)->get();

        if ($users->isEmpty()) {
            return;
        }

        // Guarantee a sample receipt exists for seed data.
        $receiptPath = 'receipts/sample-receipt.jpg';
        if (! Storage::disk('public')->exists($receiptPath)) {
            $this->makeSampleReceipt();
        }

        $products = Product::query()->orderBy('id')->take(3)->get();
        $states = ['pending_transaction', 'pending_transaction', 'pending_verification'];

        foreach ($users as $index => $user) {
            $address = $user->getDefaultAddress();
            $product = $products[$index % max($products->count(), 1)] ?? null;
            $status = $states[$index] ?? 'pending_transaction';

            $order = Order::create([
                'number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id' => $user->id,
                'product_id' => $product?->id,
                'address_id' => $address?->id,
                'customer_name' => $user->getFullNameAttribute(),
                'shipping_first_name' => $user->first_name,
                'shipping_last_name' => $user->last_name,
                'phone' => $user->phone,
                'shipping_phone' => $address?->phone ?? $user->phone,
                'total' => $product?->finalPrice() ?? 590000,
                'status' => $status,
                'placed_at' => now()->subHours($index + 1),
            ]);

            $hasReceipt = $status === 'pending_verification';

            Transaction::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $order->total,
                'gateway' => 'card',
                'payment_method' => 'card_to_card',
                'ref_id' => null,
                'tracking_code' => $hasReceipt ? (string) (1000 + $index) : null,
                'card_number' => null,
                'receipt_image' => $hasReceipt ? $receiptPath : null,
                'status' => 'pending',
                'description' => $hasReceipt
                    ? 'فیش کارت به کارت بارگذاری شد و در انتظار تأیید است.'
                    : 'در انتظار ثبت فیش کارت به کارت.',
                'created_at' => $order->placed_at,
                'updated_at' => $order->placed_at,
            ]);
        }
    }

    private function makeSampleReceipt(): void
    {
        $width = 600;
        $height = 800;
        $img = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($img, 255, 255, 255);
        $sky = imagecolorallocate($img, 14, 165, 233);
        $ink = imagecolorallocate($img, 15, 23, 42);
        $muted = imagecolorallocate($img, 100, 116, 139);
        $soft = imagecolorallocate($img, 224, 242, 254);
        $green = imagecolorallocate($img, 22, 163, 74);

        imagefill($img, 0, 0, $white);
        imagefilledrectangle($img, 0, 0, $width, 120, $sky);
        imagefilledrectangle($img, 0, 120, $width, 124, $green);

        // Latin-only text to avoid GD font shaping issues.
        imagestring($img, 5, 30, 30, 'BANK MELLAT - TRANSFER RECEIPT', $white);
        imagestring($img, 3, 30, 60, 'Asemanpoosh Online Store', $white);

        $rows = [
            'Status: SUCCESSFUL',
            'Date: 2026-11-16  14:32',
            'Amount: 590,000 Toman',
            'Ref ID: 847392016',
            'From Card: 6037-****-****-4242',
            'To Card: 6037-9975-1234-5678',
            'Tracking: 1025',
        ];

        $y = 170;
        foreach ($rows as $row) {
            imagefilledrectangle($img, 30, $y, $width - 30, $y + 56, $soft);
            imagestring($img, 5, 46, $y + 20, $row, $ink);
            $y += 70;
        }

        imagestring($img, 3, 30, $y + 30, 'This is a sample receipt generated for testing.', $muted);
        imagerectangle($img, 29, 169, $width - 29, $y - 10, $muted);

        $full = storage_path('app/public/receipts/sample-receipt.jpg');
        @mkdir(dirname($full), 0775, true);
        imagejpeg($img, $full, 90);
        imagedestroy($img);
    }
}
