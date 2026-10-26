<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Setting;
use App\Models\User;
use App\Support\Costing;
use App\Support\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostingBusyThemeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $latte;

    private Ingredient $milk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000, 'prep_minutes' => 6]);
        $this->milk = Ingredient::create(['name' => 'Susu', 'unit' => 'ml', 'stock' => 1000, 'min_stock' => 200, 'cost_per_unit' => 20]);
        $this->latte->ingredients()->attach($this->milk->id, ['quantity' => 150]);
    }

    private function settings(array $overrides = []): array
    {
        return array_merge([
            'overheads' => ['op_salary' => 3000000, 'op_utilities' => 1000000, 'op_rent' => 0, 'op_transport' => 500000, 'op_other' => 500000],
            'op_monthly_servings' => 1000,
            'busy_extra_minutes' => 10,
            'theme' => 'espresso',
        ], $overrides);
    }

    public function test_overhead_is_spread_per_serving_and_shown_in_product_form(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.update'), $this->settings())->assertRedirect();

        $this->assertSame(5000000, Costing::monthlyOverhead());
        $this->assertSame(5000, Costing::overheadPerServing());
        $this->assertSame(3000 + 5000, $this->latte->fresh('ingredients')->fullCost());

        $this->get(route('admin.products.edit', $this->latte))->assertOk()
            ->assertSee('overhead: 5000', false)
            ->assertSee('Hitung harga');
    }

    public function test_manual_cost_is_used_for_products_without_recipe(): void
    {
        $cake = Product::create(['category_id' => $this->latte->category_id, 'name' => 'Brownies', 'slug' => 'brownies', 'price' => 20000, 'manual_cost' => 9000]);
        $this->assertSame(9000, $cake->estimatedCost());

        $this->actingAs($this->admin)->post(route('cashier.store'), [
            'payment_method' => 'tunai',
            'items' => [['product_id' => $cake->id, 'quantity' => 2]],
        ]);

        $this->assertSame(9000, Order::latest('id')->first()->items->first()->cost);
    }

    public function test_report_shows_net_profit_after_overhead(): void
    {
        Setting::put('op_salary', '3000000');

        $this->actingAs($this->admin)->get(route('admin.reports.index'))->assertOk()
            ->assertSee('Laba bersih (perkiraan)')
            ->assertSee('biaya operasional');
    }

    public function test_busy_mode_from_cashier_adds_note_and_extra_minutes(): void
    {
        $this->actingAs($this->admin)->post(route('cashier.busy'))->assertRedirect(route('cashier.index'));
        $this->assertSame('1', Setting::get('busy_mode'));

        $this->get('/menu')->assertSee('mungkin sedikit terlambat');

        $this->post(route('cashier.store'), ['payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $order = Order::latest('id')->first();
        $this->assertSame(16, $order->estimatedMinutes());
        $this->get(route('menu.order', $order))->assertSee('Perkiraan siap ± 16 mnt')->assertSee('mungkin sedikit terlambat');

        $this->post(route('cashier.busy'));
        $this->assertSame(6, $order->fresh()->estimatedMinutes());
        $this->get('/menu')->assertDontSee('mungkin sedikit terlambat');
    }

    public function test_theme_overrides_staff_colors_but_not_the_customer_menu(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.update'), $this->settings(['theme' => 'matcha']));

        $this->assertStringContainsString('--color-espresso-500:#5f8a3b', Theme::css());
        $this->get('/menu')->assertDontSee('--color-espresso-500:#5f8a3b', false);
        $this->get(route('cashier.index'))->assertSee('--color-espresso-500:#5f8a3b', false);

        $this->put(route('admin.settings.update'), $this->settings(['theme' => 'custom', 'theme_color' => '#123ABC']));
        $this->get(route('admin.dashboard'))->assertSee('--color-espresso-500:#123abc', false);

        $this->put(route('admin.settings.update'), $this->settings(['theme' => 'custom', 'theme_color' => 'merah']))
            ->assertSessionHasErrors('theme_color');
    }

    public function test_recipe_page_shows_needs_stock_and_portions(): void
    {
        $this->actingAs($this->admin)->get(route('admin.recipes.index'))->assertOk()
            ->assertSee('Caffe Latte')
            ->assertSeeInOrder(['Susu', '150', '1.000', '6 porsi'])
            ->assertSee('Bisa dibuat 6 porsi')
            ->assertSee('Ubah stok');

        $this->get(route('admin.ingredients.show', $this->milk))->assertOk()
            ->assertSee('Menu yang memakai bahan ini')
            ->assertSee('cukup untuk 6 porsi');
    }

    public function test_promo_card_shows_even_without_image(): void
    {
        Promotion::create(['title' => 'Senin Hemat', 'discount_type' => 'percent', 'discount_value' => 20, 'is_active' => true]);

        $this->get('/menu')->assertOk()->assertSee('Senin Hemat')->assertSee('Diskon 20%');
    }

    public function test_settings_page_has_one_always_visible_save_button(): void
    {
        $this->actingAs($this->admin)->get(route('admin.settings.edit'))->assertOk()
            ->assertSee('sticky bottom-0', false)
            ->assertSee('Biaya operasional bulanan')
            ->assertSee('Mode ramai')
            ->assertSee('Tema warna');
    }
}
