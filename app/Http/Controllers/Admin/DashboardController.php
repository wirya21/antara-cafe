<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Support\SalesReport;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = new SalesReport(today()->startOfDay(), today()->endOfDay());
        $week = new SalesReport(today()->subDays(6)->startOfDay(), today()->endOfDay());

        return view('admin.dashboard', [
            'today' => $today->totals(),
            'topToday' => $today->topProducts(5),
            'week' => $week->daily(),
            'pendingCount' => Order::where('status', 'pending')->count(),
            'lowStock' => Ingredient::low()->orderBy('stock')->limit(8)->get(),
            'activePromotionCount' => Promotion::running()->count(),
            'menuStock' => $this->menuRunningOut(),
        ]);
    }

    /** Menu yang habis atau hampir habis karena stok bahannya, yang paling kritis dulu. */
    private function menuRunningOut()
    {
        return Product::with('ingredients')->where('is_available', true)->whereHas('ingredients')->get()
            ->map(fn (Product $product) => ['product' => $product, 'left' => $product->servingsLeft()])
            ->filter(fn ($row) => $row['left'] !== null && $row['left'] <= Product::LOW_SERVINGS)
            ->sortBy('left')
            ->map(fn ($row) => $row + ['ingredient' => $row['product']->limitingIngredient()])
            ->values();
    }
}
