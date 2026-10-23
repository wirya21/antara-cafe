<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $paidToday = Order::where('status', 'paid')->whereDate('paid_at', today());

        return view('admin.dashboard', [
            'productCount' => Product::count(),
            'categoryCount' => Category::count(),
            'activePromotionCount' => Promotion::running()->count(),
            'ordersToday' => (clone $paidToday)->count(),
            'revenueToday' => (clone $paidToday)->sum('total'),
        ]);
    }
}
