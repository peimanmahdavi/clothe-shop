<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailViewTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(string $status): Order
    {
        $user = User::factory()->create(['phone' => '09120000005', 'first_name' => 'علی', 'last_name' => 'احمدی']);

        $address = Address::create([
            'user_id' => $user->id,
            'title' => 'خانه',
            'first_name' => 'علی',
            'last_name' => 'احمدی',
            'phone' => '09120000005',
            'province' => 'تهران',
            'city' => 'تهران',
            'address_line' => 'خیابان ولیعصر',
            'is_default' => true,
        ]);

        $product = Product::create([
            'name' => 'پیراهن چهارخانه آبی و مشکی',
            'price' => 490000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::create([
            'number' => 'ORD-TEST-0001',
            'user_id' => $user->id,
            'address_id' => $address->id,
            'customer_name' => 'علی احمدی',
            'shipping_first_name' => 'علی',
            'shipping_last_name' => 'احمدی',
            'phone' => '09120000005',
            'shipping_phone' => '09120000005',
            'subtotal' => 3920000,
            'discount_amount' => 0,
            'total' => 3920000,
            'status' => $status,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'size' => 'L',
            'color' => 'آبی',
            'quantity' => 8,
            'unit_price' => 490000,
            'line_total' => 3920000,
        ]);

        Transaction::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $order->total,
            'gateway' => 'card',
            'payment_method' => 'card_to_card',
            'status' => 'pending',
        ]);

        return $order;
    }

    public function test_pending_transaction_order_shows_receipt_uploader(): void
    {
        $order = $this->makeOrder('pending_transaction');

        $this->actingAs($order->user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee('ثبت فیش واریز کارت به کارت')
            ->assertSee('ارسال فیش و تایید پرداخت')
            ->assertSee('data-receipt-input', false);
    }

    public function test_pending_verification_order_shows_submitted_preview(): void
    {
        $order = $this->makeOrder('pending_verification');

        $this->actingAs($order->user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee('فیش شما ثبت شده و در انتظار تایید مدیریت است.')
            ->assertDontSee('ارسال فیش و تایید پرداخت');
    }

    public function test_completed_order_renders_timeline_without_cancel_step(): void
    {
        $order = $this->makeOrder('completed');

        $response = $this->actingAs($order->user)->get(route('profile.orders.show', $order));

        $response->assertOk()
            ->assertSee('زمان‌بندی وضعیت')
            ->assertSee('تایید پرداخت و پردازش')
            ->assertDontSee('لغو شده');
    }

    public function test_cancelled_order_shows_cancel_step(): void
    {
        $order = $this->makeOrder('cancelled');

        $this->actingAs($order->user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee('لغو شده');
    }

    public function test_title_renders_order_number_cleanly(): void
    {
        $order = $this->makeOrder('processing');

        $this->actingAs($order->user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee('<title>سفارش #ORD-TEST-0001 | آسمان‌پوش</title>', false);
    }
}
