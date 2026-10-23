<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Papan pesanan yang sedang berjalan: masuk (belum dibayar), diproses, selesai dan batal hari ini.
 * Berbeda dengan Transaksi yang berisi riwayat keuangan.
 */
class OrderBoardController extends Controller
{
    public const TABS = [
        'masuk' => 'Masuk',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    public function index(Request $request): View
    {
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'masuk';

        $orders = $this->query($tab)
            ->with($tab === 'diproses' ? 'items.product' : 'items')
            ->limit(60)
            ->get();

        return view('admin.board.index', [
            'tab' => $tab,
            'orders' => $orders,
            'counts' => $this->tabCounts(),
        ]);
    }

    /** Dipakai halaman untuk tahu kapan perlu dimuat ulang. */
    public function counts(): JsonResponse
    {
        return response()->json($this->tabCounts() + ['latest' => (int) Order::max('id')]);
    }

    private function tabCounts(): array
    {
        return collect(self::TABS)->map(fn ($label, $tab) => $this->query($tab)->count())->all();
    }

    private function query(string $tab): Builder
    {
        return match ($tab) {
            'masuk' => Order::where('status', 'pending')->oldest(),
            'diproses' => Order::where('status', 'paid')->whereNull('completed_at')
                ->where('paid_at', '>=', now()->subDay())->oldest('paid_at'),
            'selesai' => Order::where('status', 'paid')->where('completed_at', '>=', today())->latest('completed_at'),
            'batal' => Order::where('status', 'cancelled')->where('updated_at', '>=', today())->latest('updated_at'),
        };
    }
}
