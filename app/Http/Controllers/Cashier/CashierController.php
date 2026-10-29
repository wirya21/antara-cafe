<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Busy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        $menu = Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->where('is_available', true)->with('ingredients')->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->products->isNotEmpty())
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'group' => $category->group,
                'products' => $category->products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image_url,
                    'group' => $category->group,
                    'left' => $product->servingsLeft(),
                ])->values(),
            ])->values();

        return view('cashier.index', [
            'menu' => $menu,
            'pending' => $this->pendingOrders(),
            'processing' => $this->processingOrders(),
            'paymentMethods' => Order::PAYMENT_METHODS,
            'busy' => Busy::on(),
        ]);
    }

    public function pending(): JsonResponse
    {
        return response()->json(['pending' => $this->pendingOrders(), 'processing' => $this->processingOrders()]);
    }

    /** Ringkasan untuk lencana dan bunyi notifikasi di admin/kasir. */
    public function alerts(): JsonResponse
    {
        $pending = Order::where('status', 'pending');

        return response()->json([
            'pending' => (clone $pending)->count(),
            'latest' => (int) (clone $pending)->max('id'),
            'proofs' => (clone $pending)->whereNotNull('payment_proof_path')->count(),
        ]);
    }

    public function complete(Request $request, Order $order): RedirectResponse
    {
        $order->markCompleted();

        return $this->backTo($request, $order)->with('status', "Pesanan {$order->code} ditandai siap.");
    }

    /** Nyalakan/matikan mode ramai langsung dari layar kasir. */
    public function toggleBusy(): RedirectResponse
    {
        Setting::put('busy_mode', Busy::on() ? null : '1');

        return redirect()->route('cashier.index')->with('status', Busy::on()
            ? 'Mode ramai aktif. Pelanggan diberi tahu pesanan mungkin sedikit terlambat.'
            : 'Mode ramai dimatikan.');
    }

    /** Aksi ini dipakai dari halaman kasir dan dari detail transaksi admin. */
    private function backTo(Request $request, Order $order): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->route('cashier.index');
        }

        return match ($request->input('kembali')) {
            'admin' => redirect()->route('admin.orders.show', $order),
            'pesanan' => redirect()->route('admin.board.index', ['tab' => $request->input('tab', 'masuk')]),
            default => redirect()->route('cashier.index'),
        };
    }

    public function proof(Order $order)
    {
        abort_unless($order->payment_proof_path && Storage::disk('local')->exists($order->payment_proof_path), 404);

        return Storage::disk('local')->response($order->payment_proof_path);
    }

    public function store(Request $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:50'],
            'table_number' => ['nullable', 'string', 'max:10'],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.note' => ['nullable', 'string', 'max:120'],
            'voucher_code' => ['nullable', 'string', 'max:30'],
            'member_phone' => ['nullable', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
            'paid' => ['nullable', 'boolean'],
        ], [
            'member_phone.regex' => 'Nomor HP tidak valid.',
        ]);

        // Kasir mengonfirmasi uang sudah diterima; bila belum, pesanan menunggu di tab Menunggu bayar.
        $paid = $request->boolean('paid', true);

        $order = DB::transaction(function () use ($data, $placeOrder, $request, $paid) {
            $order = $placeOrder->handle($data['items'], [
                'source' => 'kasir',
                'customer_name' => $data['customer_name'] ?? null,
                'table_number' => $data['table_number'] ?? null,
                'payment_method' => $data['payment_method'],
                'cashier_id' => $request->user()->id,
            ], $data['voucher_code'] ?? null, $data['member_phone'] ?? null);

            if ($paid) {
                $order->markPaid($data['payment_method'], $request->user());
            }

            return $order;
        });

        if (! $paid) {
            return redirect()->route('cashier.index')
                ->with('status', "Pesanan {$order->code} disimpan di Menunggu bayar. Konfirmasi setelah pembayaran diterima.");
        }

        return redirect()->route('cashier.index')
            ->with('status', "Pesanan {$order->code} dibayar.")
            ->with('receipt', $order->code);
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === 'pending', 409, 'Pesanan ini sudah diproses.');

        $data = $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ]);

        $order->markPaid($data['payment_method'], $request->user());

        return $this->backTo($request, $order)
            ->with('status', "Pembayaran {$order->code} dikonfirmasi. Pesanan diteruskan untuk dibuat.")
            ->with('receipt', $order->code);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $order->cancelPending($request->user());

        return $this->backTo($request, $order)->with('status', "Pesanan {$order->code} dibatalkan.");
    }

    public function receipt(Order $order): View
    {
        abort_unless($order->status === 'paid', 404);

        return view('cashier.receipt', ['order' => $order->load('items', 'cashier', 'member')]);
    }

    private function pendingOrders(): array
    {
        return Order::where('status', 'pending')->with('items')->oldest()->get()
            ->map(fn (Order $order) => $this->orderPayload($order))->all();
    }

    /** Sudah dibayar, belum diserahkan ke pelanggan. */
    private function processingOrders(): array
    {
        return Order::where('status', 'paid')->whereNull('completed_at')
            ->where('paid_at', '>=', now()->subDay())
            ->with('items.product')->oldest('paid_at')->get()
            ->map(fn (Order $order) => $this->orderPayload($order))->all();
    }

    private function orderPayload(Order $order): array
    {
        return [
            'code' => $order->code,
            'source' => $order->source,
            'customer_name' => $order->customer_name,
            'table_number' => $order->table_number,
            'total' => $order->total,
            'time' => ($order->paid_at ?? $order->created_at)->format('H:i'),
            // Waktu tunggu dihitung sejak pesanan masuk (belum bayar) atau sejak dibayar (sedang dibuat).
            'since' => ($order->status === 'paid' ? $order->paid_at : $order->created_at)->toIso8601String(),
            'eta' => $order->status === 'paid' ? $order->estimatedMinutes() : null,
            'payment_method' => $order->payment_method,
            'payment_label' => Order::PAYMENT_METHODS[$order->payment_method] ?? null,
            'proof_url' => $order->payment_proof_path ? route('cashier.proof', $order) : null,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'note' => $item->note,
            ]),
        ];
    }
}
