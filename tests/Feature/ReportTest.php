<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_sums_paid_orders_with_cost_and_profit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
        $coffee = Ingredient::create(['name' => 'Biji kopi', 'unit' => 'gram']);
        app(Inventory::class)->purchase($coffee, 1000, 250000);
        $latte->ingredients()->attach($coffee, ['quantity' => 20]); // HPP Rp5.000

        $this->actingAs($admin);
        $this->post('/kasir/pesanan', ['payment_method' => 'tunai', 'items' => [['product_id' => $latte->id, 'quantity' => 2]]]);
        $this->post('/kasir/pesanan', ['payment_method' => 'qris', 'items' => [['product_id' => $latte->id, 'quantity' => 1]]]);
        $this->post('/kasir/pesanan', ['payment_method' => 'qris', 'items' => [['product_id' => $latte->id, 'quantity' => 1]]]);
        $this->post(route('admin.orders.void', Order::latest('id')->first()), ['reason' => 'Tes']);

        $this->get('/admin/laporan?periode=hari-ini')
            ->assertOk()
            ->assertViewHas('totals', fn ($t) => $t['net'] === 84000 && $t['cost'] === 15000 && $t['profit'] === 69000 && $t['count'] === 2)
            ->assertSee('Caffe Latte')
            ->assertSee('QRIS');

        $csv = $this->get('/admin/laporan/export?periode=hari-ini')->assertOk()->streamedContent();
        $this->assertStringContainsString('"Caffe Latte";Kopi;3;84000;69000', $csv);

        $this->get('/admin')->assertOk()->assertSee('Rp84.000');
    }

    public function test_report_presets_render(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (['hari-ini', '7-hari', 'bulan-ini', 'bulan-lalu'] as $preset) {
            $this->get("/admin/laporan?periode={$preset}")->assertOk();
        }
        $this->get('/admin/laporan?from=2026-01-01&to=2026-01-31')->assertOk()->assertSee('31 Jan 2026');
    }
}
