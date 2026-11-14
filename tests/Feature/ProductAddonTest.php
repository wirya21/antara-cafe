<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAddonTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Product $friedRice;

    private Product $peanuts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Makanan', 'slug' => 'makanan', 'group' => 'makanan']);
        $this->friedRice = Product::create(['category_id' => $category->id, 'name' => 'Nasi Goreng', 'slug' => 'nasi-goreng', 'price' => 25000, 'is_available' => true]);
        $this->peanuts = Product::create(['category_id' => $category->id, 'name' => 'Kacang Tanah', 'slug' => 'kacang-tanah', 'price' => 10000, 'is_available' => true]);
    }

    private function saveAddons(array $addons): void
    {
        $this->actingAs($this->admin)->put(route('admin.products.update', $this->friedRice), [
            'category_id' => $this->friedRice->category_id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
            'is_available' => '1',
            'addons' => $addons,
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_sets_toppings_per_menu_and_ids_stay_when_edited(): void
    {
        $this->saveAddons([['name' => 'Bakso', 'price' => 5000], ['name' => 'Extra telur', 'price' => 4000]]);
        $bakso = $this->friedRice->addons()->where('name', 'Bakso')->first();

        $this->saveAddons([['id' => $bakso->id, 'name' => 'Bakso sapi', 'price' => 6000], ['name' => 'Sosis', 'price' => 3000]]);

        $this->assertSame(['Bakso sapi', 'Sosis'], $this->friedRice->addons()->pluck('name')->all());
        $this->assertSame(6000, $bakso->fresh()->price);
        $this->assertSame(0, $this->peanuts->addons()->count(), 'Topping hanya milik menu yang diatur.');
    }

    public function test_customer_pays_for_chosen_toppings(): void
    {
        $this->saveAddons([['name' => 'Bakso', 'price' => 5000], ['name' => 'Extra telur', 'price' => 4000]]);
        [$bakso, $egg] = $this->friedRice->addons()->get()->all();
        $this->post('/logout');

        $this->get('/menu')->assertSee('Extra telur');

        $this->post('/menu/pesan', [
            'customer_name' => 'Sari', 'payment_method' => 'tunai',
            'items' => [
                ['product_id' => $this->friedRice->id, 'quantity' => 2, 'addons' => [$bakso->id, $egg->id]],
                ['product_id' => $this->friedRice->id, 'quantity' => 1],
            ],
        ])->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame(2 * 34000 + 25000, $order->total);
        $item = $order->items->first();
        $this->assertSame(34000, $item->price);
        $this->assertSame('+ Bakso, + Extra telur', $item->addonText());

        $this->get(route('menu.order', $order))->assertSee('+ Bakso, + Extra telur');
    }

    public function test_topping_from_another_menu_is_rejected(): void
    {
        $this->saveAddons([['name' => 'Keju', 'price' => 5000]]);
        $cheese = $this->friedRice->addons()->first();
        $this->post('/logout');

        $this->post('/menu/pesan', [
            'customer_name' => 'Sari', 'payment_method' => 'tunai',
            'items' => [['product_id' => $this->peanuts->id, 'quantity' => 1, 'addons' => [$cheese->id]]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
    }

    public function test_cashier_order_with_toppings(): void
    {
        $this->saveAddons([['name' => 'Sosis', 'price' => 3000]]);
        $sausage = $this->friedRice->addons()->first();

        $this->post(route('cashier.store'), [
            'payment_method' => 'tunai',
            'items' => [['product_id' => $this->friedRice->id, 'quantity' => 1, 'addons' => [$sausage->id]]],
        ])->assertSessionHasNoErrors();

        $order = Order::firstOrFail();
        $this->assertSame(28000, $order->total);
        $this->get(route('cashier.receipt', $order))->assertSee('+ Sosis');
        $this->get(route('cashier.index'))->assertSee('Sosis');
    }
}
