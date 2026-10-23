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

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $latte;

    private Ingredient $coffee;

    private Ingredient $milk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);

        $inventory = app(Inventory::class);
        $this->coffee = Ingredient::create(['name' => 'Biji kopi', 'unit' => 'gram', 'min_stock' => 200]);
        $inventory->purchase($this->coffee, 1000, 250000); // Rp250/gram
        $this->milk = Ingredient::create(['name' => 'Susu', 'unit' => 'ml', 'min_stock' => 1000]);
        $inventory->purchase($this->milk, 2000, 40000); // Rp20/ml
    }

    private function setRecipe(): void
    {
        $this->actingAs($this->admin)->put("/admin/products/{$this->latte->id}", [
            'category_id' => $this->latte->category_id,
            'name' => 'Caffe Latte',
            'price' => 28000,
            'is_available' => '1',
            'recipe' => [
                ['ingredient_id' => $this->coffee->id, 'quantity' => 18],
                ['ingredient_id' => $this->milk->id, 'quantity' => 150],
                ['ingredient_id' => '', 'quantity' => ''],
            ],
        ])->assertRedirect();
    }

    public function test_recipe_is_saved_on_product(): void
    {
        $this->setRecipe();

        $this->assertSame(2, $this->latte->ingredients()->count());
        $this->assertSame(18 * 250 + 150 * 20, $this->latte->fresh()->load('ingredients')->estimatedCost());
    }

    public function test_paid_sale_reduces_stock_and_records_cost(): void
    {
        $this->setRecipe();

        $this->post('/kasir/pesanan', [
            'payment_method' => 'tunai',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 2]],
        ]);

        $this->assertEquals(1000 - 36, $this->coffee->fresh()->stock);
        $this->assertEquals(2000 - 300, $this->milk->fresh()->stock);
        $this->assertSame(7500, Order::first()->items->first()->cost);
        $this->assertDatabaseHas('stock_movements', ['ingredient_id' => $this->coffee->id, 'type' => 'sale', 'quantity' => -36]);
    }

    public function test_pending_customer_order_does_not_touch_stock_until_paid(): void
    {
        $this->setRecipe();
        $this->post('/logout');

        $this->post('/menu/pesan', ['customer_name' => 'Sari', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $this->assertEquals(1000, $this->coffee->fresh()->stock);

        $this->actingAs($this->admin)->post(route('cashier.pay', Order::first()), ['payment_method' => 'qris']);
        $this->assertEquals(982, $this->coffee->fresh()->stock);
    }

    public function test_void_returns_stock(): void
    {
        $this->setRecipe();
        $this->post('/kasir/pesanan', ['payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $order = Order::first();

        $this->post(route('admin.orders.void', $order), ['reason' => 'Salah input'])->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('Salah input', $order->fresh()->void_reason);
        $this->assertEquals(1000, $this->coffee->fresh()->stock);
        $this->assertEquals(2000, $this->milk->fresh()->stock);
    }

    public function test_cashier_cannot_void(): void
    {
        $this->setRecipe();
        $this->post('/kasir/pesanan', ['payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->post(route('admin.orders.void', Order::first()), ['reason' => 'x'])
            ->assertForbidden();
    }

    public function test_purchase_updates_weighted_average_cost(): void
    {
        $this->actingAs($this->admin)->post(route('admin.ingredients.move', $this->coffee), [
            'type' => 'purchase', 'quantity' => 1000, 'total_cost' => 350000,
        ])->assertRedirect();

        $coffee = $this->coffee->fresh();
        $this->assertEquals(2000, $coffee->stock);
        $this->assertEquals(300, $coffee->cost_per_unit);
    }

    public function test_stock_opname_sets_actual_stock(): void
    {
        $this->actingAs($this->admin)->post(route('admin.ingredients.move', $this->milk), [
            'type' => 'adjustment', 'quantity' => 1750, 'note' => 'Hitung akhir hari',
        ]);

        $this->assertEquals(1750, $this->milk->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', ['ingredient_id' => $this->milk->id, 'type' => 'adjustment', 'quantity' => -250]);
    }

    public function test_low_stock_is_flagged_and_pages_render(): void
    {
        app(Inventory::class)->waste($this->coffee, 900);

        $this->actingAs($this->admin)->get('/admin/stok')->assertOk()->assertSee('Menipis');
        $this->get("/admin/stok/{$this->coffee->id}")->assertOk()->assertSee('Terbuang');
        $this->get('/admin')->assertOk()->assertSee('Biji kopi');
    }

    public function test_transactions_list_and_export(): void
    {
        $this->setRecipe();
        $this->post('/kasir/pesanan', ['payment_method' => 'qris', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $order = Order::first();

        $this->get('/admin/transaksi')->assertOk()->assertSee($order->code);
        $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('Caffe Latte');

        $csv = $this->get('/admin/transaksi/export')->assertOk()->streamedContent();
        $this->assertStringContainsString($order->code, $csv);
        $this->assertStringContainsString('1x Caffe Latte', $csv);
    }
}
