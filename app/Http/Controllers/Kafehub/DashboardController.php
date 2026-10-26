<?php

namespace App\Http\Controllers\Kafehub;

use App\Http\Controllers\Controller;
use App\Models\Cafe;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/** Ringkasan semua kafe untuk pengelola kafehub. */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $cafes = Cafe::orderBy('name')->get();
        $byStatus = $cafes->countBy(fn (Cafe $cafe) => $cafe->status());

        $paid = fn () => Order::where('status', 'paid');
        $since = now()->subDays(29)->startOfDay();

        // Pesanan dibayar per hari, 14 hari terakhir, semua kafe.
        $daily = $paid()->where('paid_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['paid_at'])
            ->countBy(fn (Order $order) => $order->paid_at->toDateString());
        $chart = collect(range(13, 0))->map(function ($ago) use ($daily) {
            $day = now()->subDays($ago);

            return ['date' => $day, 'count' => $daily[$day->toDateString()] ?? 0];
        });

        $perCafe = $paid()->where('paid_at', '>=', $since)
            ->selectRaw('cafe_id, count(*) as orders, sum(total) as revenue, max(paid_at) as last_paid')
            ->groupBy('cafe_id')->get()->keyBy('cafe_id');

        $top = $cafes->map(fn (Cafe $cafe) => [
            'cafe' => $cafe,
            'orders' => (int) ($perCafe[$cafe->id]->orders ?? 0),
            'revenue' => (int) ($perCafe[$cafe->id]->revenue ?? 0),
            'last' => isset($perCafe[$cafe->id]) ? Carbon::parse($perCafe[$cafe->id]->last_paid) : null,
        ])->sortByDesc('revenue')->values();

        return view('panel.dashboard', [
            'total' => $cafes->count(),
            'active' => ($byStatus['aktif'] ?? 0) + ($byStatus['segera'] ?? 0),
            'trial' => $byStatus['trial'] ?? 0,
            'attention' => $cafes->filter(fn (Cafe $cafe) => in_array($cafe->status(), ['segera', 'berakhir'], true)
                || ($cafe->status() === 'trial' && $cafe->daysLeft() !== null && $cafe->daysLeft() <= 7))
                ->sortBy(fn (Cafe $cafe) => $cafe->active_until)->values(),
            'mrr' => $cafes->filter(fn (Cafe $cafe) => in_array($cafe->status(), ['aktif', 'segera'], true))->sum('monthly_fee'),
            'ordersToday' => $paid()->where('paid_at', '>=', today())->count(),
            'revenue30' => (int) $paid()->where('paid_at', '>=', $since)->sum('total'),
            'chart' => $chart,
            'top' => $top,
        ]);
    }
}
