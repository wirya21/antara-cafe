<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $query = $this->filtered($request, $from, $to);

        $paid = (clone $query)->where('status', 'paid');

        return view('admin.orders.index', [
            'orders' => (clone $query)->with('cashier')->withCount('items')->latest('id')->paginate(30)->withQueryString(),
            'from' => $from,
            'to' => $to,
            'summary' => [
                'count' => (clone $paid)->count(),
                'revenue' => (int) (clone $paid)->sum('total'),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            ],
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items', 'cashier', 'member', 'promotion'),
            'voidedBy' => $order->voided_by ? \App\Models\User::find($order->voided_by) : null,
        ]);
    }

    public function void(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === 'paid', 409, 'Hanya transaksi yang sudah dibayar yang bisa di-void.');

        $data = $request->validate(['reason' => ['required', 'string', 'max:150']], [
            'reason.required' => 'Tulis alasan pembatalan.',
        ]);

        $order->void($request->user(), $data['reason']);

        return back()->with('status', "Transaksi {$order->code} dibatalkan dan stok bahan dikembalikan.");
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);
        $orders = $this->filtered($request, $from, $to)->with('items', 'cashier')->oldest('id');

        $filename = 'transaksi-antara-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['Kode', 'Waktu', 'Status', 'Sumber', 'Pelanggan', 'Meja', 'Item', 'Subtotal', 'Diskon', 'Total', 'HPP', 'Metode bayar', 'Kasir'], ';');

            $orders->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $order) {
                    fputcsv($out, [
                        $order->code,
                        $order->created_at->format('Y-m-d H:i'),
                        Order::STATUSES[$order->status] ?? $order->status,
                        $order->source === 'self_order' ? 'Pelanggan' : 'Kasir',
                        $order->customer_name,
                        $order->table_number,
                        $order->items->map(fn ($i) => "{$i->quantity}x {$i->product_name}")->join(', '),
                        $order->subtotal,
                        $order->discount,
                        $order->total,
                        $order->items->sum(fn ($i) => $i->cost * $i->quantity),
                        Order::PAYMENT_METHODS[$order->payment_method] ?? $order->payment_method,
                        $order->cashier?->name,
                    ], ';');
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function period(Request $request): array
    {
        $from = $request->date('from') ?? today();
        $to = $request->date('to') ?? today();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->endOfDay()];
    }

    private function filtered(Request $request, Carbon $from, Carbon $to): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->input('source')))
            ->when($request->filled('payment'), fn ($q) => $q->where('payment_method', $request->input('payment')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q
                ->where('code', 'like', '%'.$request->string('q').'%')
                ->orWhere('customer_name', 'like', '%'.$request->string('q').'%')));
    }
}
