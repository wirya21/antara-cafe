<?php

namespace App\Http\Controllers\Customer;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Setting;
use App\Support\Busy;
use App\Support\WhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->with('ingredients')->orderBy('sort_order')->orderBy('name')])
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
                'available' => $product->isOrderable(),
                'left' => $product->servingsLeft(),
                'featured' => $product->is_featured,
                'prep' => $product->prep_minutes,
                'group' => $category->group,
            ])->values(),
        ])->values();

        return view('customer.menu', [
            'menu' => $menu,
            'banners' => Promotion::running()->latest()->get(),
            'autoPromos' => Promotion::running()->whereNotNull('discount_type')->whereNull('voucher_code')->get()
                ->map(fn ($p) => $this->promoPayload($p))->values(),
            'table' => $request->string('meja')->limit(10, '')->toString(),
            'paymentMethods' => Order::availableSelfPaymentMethods(),
            'popup' => $this->popupPayload(),
            'busyMessage' => Busy::message(),
        ]);
    }

    /** Promo yang dipilih admin untuk tampil sebagai kartu saat menu dibuka. */
    private function popupPayload(): ?array
    {
        $promo = Promotion::running()->where('show_popup', true)->latest('updated_at')->first();

        return $promo ? [
            'key' => $promo->id.'-'.$promo->updated_at->timestamp,
            'title' => $promo->title,
            'description' => $promo->description,
            'image' => $promo->imageUrl(),
            'summary' => $promo->discount_type ? $promo->summary() : null,
            'voucher' => $promo->voucher_code,
            'until' => $promo->ends_at?->translatedFormat('d F Y'),
        ] : null;
    }

    public function store(Request $request, PlaceOrder $placeOrder, WhatsApp $whatsApp): RedirectResponse
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
            'payment_method' => ['required', Rule::in(array_keys(Order::availableSelfPaymentMethods()))],
        ], [
            'member_phone.regex' => 'Nomor HP tidak valid.',
            'payment_method.required' => 'Pilih cara pembayaran.',
            'customer_name.required' => 'Isi nama Anda supaya kami bisa memanggil saat pesanan siap.',
            'items.required' => 'Keranjang masih kosong.',
        ]);

        $order = $placeOrder->handle($data['items'], [
            'source' => 'self_order',
            'customer_name' => $data['customer_name'],
            'table_number' => $data['table_number'] ?? null,
            'payment_method' => $data['payment_method'],
        ], $data['voucher_code'] ?? null, $data['member_phone'] ?? null);

        $whatsApp->notifyLater($order, '🛎️ Pesanan baru '.$order->code);

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
        // Pesanan kasir juga bisa dilacak lewat QR di struk, asal sudah dibayar.
        abort_unless($order->source === 'self_order' || $order->status === 'paid', 404);

        $qris = Setting::get('qris_image_path');

        return view('customer.order', [
            'order' => $order->load('items.product'),
            'qrisUrl' => $qris ? Storage::disk('public')->url($qris) : null,
            'bankAccounts' => Setting::get('bank_accounts'),
            'paymentMethods' => Order::availableSelfPaymentMethods(),
            'estimate' => $order->estimatedMinutes(),
            'busyMessage' => Busy::message(),
        ]);
    }

    /** Status terbaru untuk halaman pesanan pelanggan (dicek berkala). */
    public function status(Order $order): JsonResponse
    {
        abort_unless($order->source === 'self_order' || $order->status === 'paid', 404);

        return response()->json([
            'stage' => $order->stage(),
            'has_proof' => filled($order->payment_proof_path),
        ]);
    }

    /** Riwayat pesanan di perangkat ini, tanpa harus jadi member. Kodenya disimpan di HP pelanggan. */
    public function history(): View
    {
        return view('customer.history');
    }

    public function historyData(Request $request): JsonResponse
    {
        $codes = collect(explode(',', (string) $request->query('kode')))
            ->map(fn ($code) => strtoupper(trim($code)))
            ->filter(fn ($code) => preg_match('/^[A-Z]-[A-Z0-9]{4,8}$/', $code))
            ->unique()->take(30);

        $orders = Order::whereIn('code', $codes)->with('items')->latest('id')->get()
            ->filter(fn (Order $order) => $order->source === 'self_order' || $order->status === 'paid')
            ->map(fn (Order $order) => [
                'code' => $order->code,
                'stage' => $order->stage(),
                'stage_label' => Order::CUSTOMER_STAGES[$order->stage()],
                'total' => $order->total,
                'date' => $order->created_at->translatedFormat('d M Y, H:i'),
                'items' => $order->items->map(fn ($item) => $item->quantity.'× '.$item->product_name)->join(', '),
                'url' => route('menu.order', $order),
            ])->values();

        return response()->json(['orders' => $orders]);
    }

    public function changeMethod(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->source === 'self_order' && $order->status === 'pending', 409, 'Pesanan ini sudah diproses.');

        $data = $request->validate(['payment_method' => ['required', Rule::in(array_keys(Order::availableSelfPaymentMethods()))]]);
        $order->update(['payment_method' => $data['payment_method']]);

        return redirect()->route('menu.order', $order);
    }

    public function uploadProof(Request $request, Order $order, WhatsApp $whatsApp): RedirectResponse
    {
        abort_unless($order->source === 'self_order' && $order->needsProof(), 409, 'Bukti bayar tidak diperlukan untuk pesanan ini.');

        $request->validate(['proof' => ['required', 'image', 'max:5120']], [
            'proof.required' => 'Pilih foto atau screenshot bukti pembayaran.',
            'proof.image' => 'Bukti harus berupa gambar.',
            'proof.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        // Disimpan di disk privat: hanya staf yang bisa membukanya.
        $old = $order->payment_proof_path;
        $order->update(['payment_proof_path' => $request->file('proof')->store('bukti-bayar', 'local')]);
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        $whatsApp->notifyLater($order, '💳 Bukti bayar masuk '.$order->code.' (cek & konfirmasi)');

        return redirect()->route('menu.order', $order)->with('status', 'Bukti pembayaran terkirim. Kami cek sebentar ya.');
    }
}
