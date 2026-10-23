<?php

namespace App\Http\Controllers\Customer;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
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
            'autoPromos' => Promotion::running()->whereNotNull('discount_type')->whereNull('voucher_code')->get()
                ->map(fn ($p) => $this->promoPayload($p))->values(),
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
            'voucher_code' => ['nullable', 'string', 'max:30'],
            'member_phone' => ['nullable', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
        ], [
            'member_phone.regex' => 'Nomor HP tidak valid.',
            'customer_name.required' => 'Isi nama Anda supaya kami bisa memanggil saat pesanan siap.',
            'items.required' => 'Keranjang masih kosong.',
        ]);

        $order = $placeOrder->handle($data['items'], [
            'source' => 'self_order',
            'customer_name' => $data['customer_name'],
            'table_number' => $data['table_number'] ?? null,
        ], $data['voucher_code'] ?? null, $data['member_phone'] ?? null);

        return redirect()->route('menu.order', $order);
    }

    /** Cek kode voucher dari keranjang pelanggan (untuk pratinjau diskon). */
    public function voucher(Request $request): JsonResponse
    {
        $promo = Promotion::findVoucher((string) $request->query('code', ''));

        return $promo
            ? response()->json($this->promoPayload($promo))
            : response()->json(['message' => 'Kode voucher tidak berlaku atau sudah berakhir.'], 404);
    }

    private function promoPayload(Promotion $promo): array
    {
        return [
            'title' => $promo->title,
            'type' => $promo->discount_type,
            'value' => $promo->discount_value,
            'min' => $promo->min_purchase,
            'summary' => $promo->summary(),
        ];
    }

    public function show(Order $order): View
    {
        abort_unless($order->source === 'self_order', 404);

        return view('customer.order', ['order' => $order->load('items')]);
    }
}
