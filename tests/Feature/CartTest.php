<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'تیشرت نخی',
            'price' => 500000,
            'stock' => 10,
            'is_active' => true,
        ], $overrides));
    }

    private function user(): User
    {
        return User::factory()->create(['phone' => '09120000001']);
    }

    public function test_adding_a_product_creates_a_cart_line(): void
    {
        $user = $this->user();
        $product = $this->makeProduct();

        $this->actingAs($user);
        app(CartService::class)->add($product, 2, 'M', 'آبی');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'size' => 'M',
            'color' => 'آبی',
            'quantity' => 2,
        ]);
    }

    public function test_adding_the_same_variant_increments_quantity(): void
    {
        $user = $this->user();
        $product = $this->makeProduct();

        $this->actingAs($user);
        $service = app(CartService::class);
        $service->add($product, 1, 'M', 'آبی');
        $service->add($product, 2, 'M', 'آبی');

        $this->assertSame(1, CartItem::count());
        $this->assertSame(3, (int) CartItem::first()->quantity);
    }

    public function test_different_variants_create_separate_lines(): void
    {
        $user = $this->user();
        $product = $this->makeProduct();

        $this->actingAs($user);
        $service = app(CartService::class);
        $service->add($product, 1, 'M', 'آبی');
        $service->add($product, 1, 'L', 'آبی');

        $this->assertSame(2, CartItem::count());
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $user = $this->user();
        $product = $this->makeProduct(['stock' => 2]);

        $this->actingAs($user);

        $this->expectException(RuntimeException::class);

        app(CartService::class)->add($product, 3, 'M', 'آبی');
    }

    public function test_quantity_is_capped_across_lines_of_same_product(): void
    {
        $user = $this->user();
        $product = $this->makeProduct(['stock' => 2]);

        $this->actingAs($user);
        $service = app(CartService::class);
        $service->add($product, 2, 'M', 'آبی');

        // A second line of the same product must respect remaining stock (0).
        $this->expectException(RuntimeException::class);
        $service->add($product, 1, 'L', 'آبی');
    }

    public function test_discount_applies_only_to_eligible_lines(): void
    {
        $user = $this->user();
        $eligible = $this->makeProduct(['price' => 100000]);
        $other = $this->makeProduct(['name' => 'شلوار', 'price' => 100000]);

        $discount = Discount::create([
            'title' => 'تخفیف تیشرت',
            'code' => 'TEE10',
            'percentage' => 10,
            'applies_to' => 'products',
            'product_ids' => [$eligible->id],
            'is_active' => true,
        ]);

        $this->actingAs($user);
        $service = app(CartService::class);
        $service->add($eligible, 1);
        $service->add($other, 1);

        $cart = app(CartService::class)->resolve(false);
        $cart->load('items.product');

        $result = app(DiscountService::class)->resolve('tee10', $cart->items, $cart->subtotal());

        $this->assertNotNull($result['discount']);
        // 10% of the 100,000 eligible line only.
        $this->assertSame(10000, $result['amount']);
    }

    public function test_invalid_discount_code_returns_no_discount(): void
    {
        $user = $this->user();
        $product = $this->makeProduct();

        $this->actingAs($user);
        app(CartService::class)->add($product, 1);

        $cart = app(CartService::class)->resolve(false);
        $cart->load('items.product');

        $result = app(DiscountService::class)->resolve('NOPE', $cart->items, $cart->subtotal());

        $this->assertNull($result['discount']);
        $this->assertSame(0, $result['amount']);
    }
}
