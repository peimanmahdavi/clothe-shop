<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    private function userWithAddress(): User
    {
        $user = User::factory()->create([
            'phone' => '09120000002',
            'first_name' => 'علی',
            'last_name' => 'احمدی',
        ]);

        Address::create([
            'user_id' => $user->id,
            'title' => 'خانه',
            'first_name' => 'علی',
            'last_name' => 'احمدی',
            'phone' => '09120000002',
            'province' => 'تهران',
            'city' => 'تهران',
            'address_line' => 'خیابان ولیعصر',
            'is_default' => true,
        ]);

        return $user->fresh();
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'تیشرت نخی',
            'price' => 500000,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_card_to_card_order_redirects_to_receipt_page(): void
    {
        $user = $this->userWithAddress();
        $product = $this->product();
        $address = $user->addresses()->first();

        $this->actingAs($user);

        // Put an item in the cart via the public endpoint.
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertOk();

        $response = $this->post(route('profile.orders.store'), [
            'address_id' => $address->id,
            'payment_method' => 'card_to_card',
        ]);

        $order = Order::latest('id')->first();

        $this->assertNotNull($order, 'Order was not created.');
        $this->assertSame('pending_transaction', $order->status);
        $this->assertSame(2, (int) $order->items()->sum('quantity'));
        $this->assertSame(1000000, (int) $order->total);

        $response->assertRedirect(route('profile.orders.receipt', $order));

        // The receipt page (the previously crashing path) must render.
        $this->get(route('profile.orders.receipt', $order))->assertOk();
    }

    public function test_empty_cart_redirects_back_to_cart(): void
    {
        $user = $this->userWithAddress();
        $address = $user->addresses()->first();

        $this->actingAs($user);

        $this->post(route('profile.orders.store'), [
            'address_id' => $address->id,
            'payment_method' => 'card_to_card',
        ])->assertRedirect(route('cart.index'));

        $this->assertSame(0, Order::count());
    }
}
