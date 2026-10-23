<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuStockTest extends TestCase
{
    use RefreshDatabase;

    private Product $latte;

    private Ingredient $beans;

    private Ingredient $milk;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000, 'is_featured' => true]);
        $this->beans = Ingredient::create(['name' => 'Biji kopi', 'unit' => 'gram', 'stock' => 100, 'min_stock' => 0]);
        $this->milk = Ingredient::create(['name' => 'Susu', 'unit' => 'ml', 'stock' => 450, 'min_stock' => 0]);
        $this->latte->ingredients()->attach([$this->beans->id => ['quantity' => 18], $this->milk->id => ['quantity' => 150]]);
    }

    private function order(int $quantity)
    {
        return $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'payment_method' => 'tunai',
            'items' => [['product_id' => $this->latte->id, 'quantity' => $quantity]],
        ]);
    }

    public function test_servings_left_follow_the_scarcest_ingredient(): void
    {
        $latte = $this->latte->fresh('ingredients');

        // Biji kopi cukup 5 porsi, susu cukup 3 porsi.
        $this->assertSame(3, $latte->servingsLeft());
        $this->assertSame('Susu', $latte->limitingIngredient()->name);
        $this->assertTrue($latte->isOrderable());
    }

    public function test_product_without_recipe_stays_available(): void
    {
        $tea = Product::create(['category_id' => $this->latte->category_id, 'name' => 'Teh', 'slug' => 'teh', 'price' => 10000])->fresh();

        $this->assertNull($tea->servingsLeft());
        $this->assertTrue($tea->isOrderable());
    }

    public function test_menu_marks_low_and_sold_out_items(): void
    {
        $this->get('/menu')->assertOk()->assertSee('\u0022left\u0022:3', false)->assertSee('\u0022available\u0022:true', false);

        $this->milk->update(['stock' => 100]);

        $this->get('/menu')->assertOk()->assertSee('\u0022left\u0022:0', false)->assertSee('\u0022available\u0022:false', false);
        $this->get('/layar')->assertOk()->assertSee('HABIS');
    }

    public function test_order_over_stock_is_rejected(): void
    {
        $this->order(4)->assertSessionHasErrors(['items' => 'Caffe Latte hanya tersisa 3 porsi.']);
        $this->assertSame(0, Order::count());

        $this->order(3)->assertSessionHasNoErrors();
        $this->assertSame(1, Order::count());
    }

    public function test_sold_out_item_cannot_be_ordered(): void
    {
        $this->milk->update(['stock' => 0]);

        $this->order(1)->assertSessionHasErrors(['items' => 'Caffe Latte sedang habis.']);
    }

    public function test_shared_ingredient_is_checked_across_the_whole_order(): void
    {
        $cappuccino = Product::create(['category_id' => $this->latte->category_id, 'name' => 'Cappuccino', 'slug' => 'cappuccino', 'price' => 26000]);
        $cappuccino->ingredients()->attach($this->milk->id, ['quantity' => 150]);

        $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'payment_method' => 'tunai',
            'items' => [
                ['product_id' => $this->latte->id, 'quantity' => 2],
                ['product_id' => $cappuccino->id, 'quantity' => 2],
            ],
        ])->assertSessionHasErrors(['items' => 'Stok Susu tidak cukup untuk semua pesanan ini. Kurangi jumlahnya ya.']);
    }

    public function test_admin_dashboard_lists_menu_running_out_with_cause(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Menu habis / hampir habis')
            ->assertSee('Caffe Latte')
            ->assertSee('Sisa 3 porsi')
            ->assertSeeInOrder(['Bahan penyebab:', 'Susu']);
    }
}
