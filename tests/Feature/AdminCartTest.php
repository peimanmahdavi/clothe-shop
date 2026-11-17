<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCartTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'phone' => '09120000010',
            'is_admin' => true,
        ]);
    }

    private function customerCart(): Cart
    {
        $user = User::factory()->create(['phone' => '09120000011']);

        return Cart::create([
            'user_id' => $user->id,
            'session_id' => 'user:'.$user->id,
        ]);
    }

    private function product(int $stock = 10): Product
    {
        return Product::create([
            'name' => 'تیشرت نخی',
            'price' => 500000,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_carts_index(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.carts.index'))->assertOk();
    }

    public function test_admin_can_add_an_item_to_a_customer_cart(): void
    {
        $admin = $this->admin();
        $cart = $this->customerCart();
        $product = $this->product();

        $this->actingAs($admin);

        $this->post(route('admin.carts.items.add', $cart), [
            'product_id' => $product->id,
            'quantity' => 2,
            'size' => 'M',
        ])->assertRedirect(route('admin.carts.show', $cart));

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_admin_cannot_exceed_stock(): void
    {
        $admin = $this->admin();
        $cart = $this->customerCart();
        $product = $this->product(stock: 1);

        $this->actingAs($admin);

        $this->post(route('admin.carts.items.add', $cart), [
            'product_id' => $product->id,
            'quantity' => 5,
        ])->assertSessionHasErrors('cart');

        $this->assertSame(0, CartItem::count());
    }

    public function test_admin_can_update_quantity(): void
    {
        $admin = $this->admin();
        $cart = $this->customerCart();
        $product = $this->product();

        $this->actingAs($admin);
        app(CartService::class)->addToCart($cart, $product, 1, 'M', '');

        $item = $cart->items()->first();

        $this->put(route('admin.carts.items.update', [$cart, $item->id]), [
            'quantity' => 4,
        ])->assertRedirect(route('admin.carts.show', $cart));

        $this->assertSame(4, (int) $item->fresh()->quantity);
    }

    public function test_admin_can_remove_an_item_and_clear_cart(): void
    {
        $admin = $this->admin();
        $cart = $this->customerCart();
        $productA = $this->product();
        $productB = $this->product();

        $this->actingAs($admin);
        $service = app(CartService::class);
        $service->addToCart($cart, $productA, 1, 'M', '');
        $service->addToCart($cart, $productB, 1, 'L', '');

        $first = $cart->items()->first();

        $this->delete(route('admin.carts.items.remove', [$cart, $first->id]))
            ->assertRedirect(route('admin.carts.show', $cart));
        $this->assertSame(1, $cart->items()->count());

        $this->delete(route('admin.carts.clear', $cart))
            ->assertRedirect(route('admin.carts.show', $cart));
        $this->assertSame(0, $cart->items()->count());
    }
}
