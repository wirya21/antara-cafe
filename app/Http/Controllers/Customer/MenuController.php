<?php

namespace App\Http\Controllers\Customer;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->whereHas('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $menu = $categories->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->name,
            'group' => $category->group,
            'products' => $category->products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $product->image_url,
                'available' => $product->is_available,
                'featured' => $product->is_featured,
                'group' => $category->group,
            ])->values(),
        ])->values();

        return view('customer.menu', [
            'menu' => $menu,
            'banners' => Promotion::running()->whereNotNull('image_path')->latest()->get(),
            'table' => $request->string('meja')->limit(10, '')->toString(),
        ]);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:50'],
            'table_number' => ['nullable', 'string', 'max:10'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
            'items.*.note' => ['nullable', 'string', 'max:120'],
        ], [
            'customer_name.required' => 'Isi nama Anda supaya kami bisa memanggil saat pesanan siap.',
            'items.required' => 'Keranjang masih kosong.',
        ]);

        $order = $placeOrder->handle($data['items'], [
            'source' => 'self_order',
            'customer_name' => $data['customer_name'],
            'table_number' => $data['table_number'] ?? null,
        ]);

        return redirect()->route('menu.order', $order);
    }

    public function show(Order $order): View
    {
        abort_unless($order->source === 'self_order', 404);

        return view('customer.order', ['order' => $order->load('items')]);
    }
}
