<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
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
        ]);
    }
}
