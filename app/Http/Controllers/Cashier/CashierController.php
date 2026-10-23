<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\PlaceOrder;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashierController extends Controller
{
    public function index(): View
    {
        $menu = Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order')->orderBy('name')])
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
                ])->values(),
            ])->values();

        return view('cashier.index', [
            'menu' => $menu,
            'pending' => $this->pendingOrders(),
            'paymentMethods' => Order::PAYMENT_METHODS,
        ]);
    }

    public function pending(): JsonResponse
    {
        return response()->json($this->pendingOrders());
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
        ], [
            'member_phone.regex' => 'Nomor HP tidak valid.',
        ]);

        $order = DB::transaction(function () use ($data, $placeOrder, $request) {
            $order = $placeOrder->handle($data['items'], [
                'source' => 'kasir',
                'customer_name' => $data['customer_name'] ?? null,
                'table_number' => $data['table_number'] ?? null,
            ], $data['voucher_code'] ?? null, $data['member_phone'] ?? null);
            $order->markPaid($data['payment_method'], $request->user());

            return $order;
        });

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

        return redirect()->route('cashier.index')
            ->with('status', "Pesanan {$order->code} dibayar.")
            ->with('receipt', $order->code);
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === 'pending', 409, 'Pesanan ini sudah diproses.');

        $order->update(['status' => 'cancelled', 'cashier_id' => $request->user()->id]);

        return redirect()->route('cashier.index')->with('status', "Pesanan {$order->code} dibatalkan.");
    }

    public function receipt(Order $order): View
    {
        abort_unless($order->status === 'paid', 404);

        return view('cashier.receipt', ['order' => $order->load('items', 'cashier', 'member')]);
    }

    private function pendingOrders(): array
    {
        return Order::where('status', 'pending')
            ->with('items')
            ->oldest()
            ->get()
            ->map(fn (Order $order) => [
                'code' => $order->code,
                'customer_name' => $order->customer_name,
                'table_number' => $order->table_number,
                'total' => $order->total,
                'time' => $order->created_at->format('H:i'),
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'note' => $item->note,
                ]),
            ])->all();
    }
}
