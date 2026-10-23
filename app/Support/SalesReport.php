<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Ringkasan penjualan untuk satu periode. Dihitung dari pesanan yang dibayar.
 */
class SalesReport
{
    public readonly Collection $orders;

    public function __construct(public readonly Carbon $from, public readonly Carbon $to)
    {
        $this->orders = Order::with('items.product.category')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->get();
    }

    public function totals(): array
    {
        $gross = (int) $this->orders->sum('subtotal');
        $discount = (int) $this->orders->sum('discount');
        $net = (int) $this->orders->sum('total');
        $cost = (int) $this->orders->sum(fn ($o) => $o->items->sum(fn ($i) => $i->cost * $i->quantity));
        $count = $this->orders->count();

        return [
            'gross' => $gross,
            'discount' => $discount,
            'net' => $net,
            'cost' => $cost,
            'profit' => $net - $cost,
            'count' => $count,
            'average' => $count ? (int) round($net / $count) : 0,
            'items' => (int) $this->orders->sum(fn ($o) => $o->items->sum('quantity')),
        ];
    }

    /** @return Collection<string, array{date: Carbon, revenue: int, count: int}> */
    public function daily(): Collection
    {
        $byDay = $this->orders->groupBy(fn ($o) => $o->paid_at->toDateString());
        $days = collect();

        for ($day = $this->from->copy()->startOfDay(); $day->lte($this->to); $day->addDay()) {
            $orders = $byDay->get($day->toDateString(), collect());
            $days->put($day->toDateString(), [
                'date' => $day->copy(),
                'revenue' => (int) $orders->sum('total'),
                'count' => $orders->count(),
            ]);
        }

        return $days;
    }

    /** @return Collection<int, array{hour: int, revenue: int, count: int}> */
    public function hourly(): Collection
    {
        $byHour = $this->orders->groupBy(fn ($o) => (int) $o->paid_at->format('G'));

        return collect(range(0, 23))->map(fn ($h) => [
            'hour' => $h,
            'revenue' => (int) $byHour->get($h, collect())->sum('total'),
            'count' => $byHour->get($h, collect())->count(),
        ]);
    }

    public function byPaymentMethod(): Collection
    {
        return $this->orders->groupBy('payment_method')
            ->map(fn ($orders, $method) => [
                'label' => Order::PAYMENT_METHODS[$method] ?? $method,
                'revenue' => (int) $orders->sum('total'),
                'count' => $orders->count(),
            ])
            ->sortByDesc('revenue')
            ->values();
    }

    public function topProducts(int $limit = 10): Collection
    {
        return $this->orders->flatMap->items
            ->groupBy('product_name')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'category' => $items->first()->product?->category?->name,
                'quantity' => (int) $items->sum('quantity'),
                'revenue' => (int) $items->sum('subtotal'),
                'profit' => (int) $items->sum(fn ($i) => $i->subtotal - $i->cost * $i->quantity),
            ])
            ->sortByDesc('quantity')
            ->take($limit)
            ->values();
    }

    public function byCategory(): Collection
    {
        return $this->orders->flatMap->items
            ->groupBy(fn ($i) => $i->product?->category?->name ?? 'Lainnya')
            ->map(fn ($items, $name) => ['label' => $name, 'revenue' => (int) $items->sum('subtotal'), 'count' => (int) $items->sum('quantity')])
            ->sortByDesc('revenue')
            ->values();
    }
}
