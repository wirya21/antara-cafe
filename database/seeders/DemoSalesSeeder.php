<?php

namespace Database\Seeders;

use App\Actions\PlaceOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Transaksi contoh 30 hari terakhir untuk mencoba laporan. Jangan dijalankan di server produksi.
 *
 *   php artisan db:seed --class=DemoSalesSeeder
 */
class DemoSalesSeeder extends Seeder
{
    public function run(PlaceOrder $placeOrder): void
    {
        $products = Product::where('is_available', true)->get();
        $cashier = User::where('role', 'admin')->firstOrFail();
        $methods = ['tunai', 'qris', 'qris', 'qris', 'transfer', 'kartu'];
        $today = Carbon::today();
        $now = Carbon::now();

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $count = random_int(8, 22) + ($today->copy()->subDays($daysAgo)->isWeekend() ? 10 : 0);

            for ($n = 0; $n < $count; $n++) {
                $time = $today->copy()->subDays($daysAgo)->setTime(random_int(8, 21), random_int(0, 59));
                if ($time->gt($now)) {
                    continue;
                }
                Carbon::setTestNow($time);

                $items = $products->random(random_int(1, 3))->map(fn ($p) => ['product_id' => $p->id, 'quantity' => random_int(1, 2)])->all();
                $order = $placeOrder->handle($items, ['source' => random_int(0, 1) ? 'self_order' : 'kasir', 'customer_name' => fake('id_ID')->firstName()]);
                $order->markPaid($methods[array_rand($methods)], $cashier);
            }
        }

        Carbon::setTestNow();
        $this->command?->info(Order::count().' transaksi contoh dibuat.');
    }
}
