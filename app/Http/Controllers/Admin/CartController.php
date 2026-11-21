<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddCartItemRequest;
use App\Http\Requests\Admin\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    /**
     * List customer carts that currently hold items.
     */
    public function index(Request $request): View
    {
        $query = Cart::query()
            ->whereHas('items')
            ->with(['user', 'items.product'])
            ->withCount('items');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->string('search')->trim().'%';
            $q->whereHas('user', function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term);
            });
        });

        $carts = $query->latest('updated_at')->paginate(15)->withQueryString();

        return view('admin.carts.index', [
            'carts' => $carts,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Edit a single cart: view, add, update quantities, remove lines.
     */
    public function show(Cart $cart): View
    {
        $cart->load(['user', 'items.product.images']);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'sale_price', 'stock', 'sizes', 'colors']);

        return view('admin.carts.show', [
            'cart' => $cart,
            'products' => $products,
        ]);
    }

    /**
     * Add a product to a customer's cart.
     */
    public function addItem(AddCartItemRequest $request, Cart $cart): RedirectResponse
    {
        $data = $request->validated();

        $product = Product::findOrFail($data['product_id']);

        try {
            $this->cartService->addToCart(
                $cart,
                $product,
                (int) $data['quantity'],
                $data['size'] ?? null,
                $data['color'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['cart' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.carts.show', $cart)
            ->with('status', 'کالا به سبد مشتری اضافه شد.');
    }

    /**
     * Update the quantity of a line item.
     */
    public function updateItem(UpdateCartItemRequest $request, Cart $cart, int $item): RedirectResponse
    {
        $data = $request->validated();

        $this->cartService->setItemQuantity($cart, $item, (int) $data['quantity']);

        return redirect()
            ->route('admin.carts.show', $cart)
            ->with('status', 'تعداد کالا بهروزرسانی شد.');
    }

    /**
     * Remove a line item from the cart.
     */
    public function removeItem(Cart $cart, int $item): RedirectResponse
    {
        $this->cartService->removeItem($cart, $item);

        return redirect()
            ->route('admin.carts.show', $cart)
            ->with('status', 'کالا از سبد مشتری حذف شد.');
    }

    /**
     * Empty the whole cart.
     */
    public function clear(Cart $cart): RedirectResponse
    {
        $this->cartService->emptyCart($cart);

        return redirect()
            ->route('admin.carts.show', $cart)
            ->with('status', 'سبد خرید مشتری خالی شد.');
    }
}
