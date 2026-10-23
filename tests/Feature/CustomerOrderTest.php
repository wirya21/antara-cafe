<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    private Product $latte;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
    }

    public function test_menu_is_open_without_login(): void
    {
        $this->get('/menu?meja=7')->assertOk()->assertSee('Caffe Latte');
        $this->get('/')->assertRedirect('/menu');
    }

    public function test_customer_can_place_order_without_login(): void
    {
        $response = $this->post('/menu/pesan', [
            'customer_name' => 'Budi',
            'table_number' => '7',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 2, 'note' => 'less sugar']],
        ]);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('menu.order', $order));

        $this->assertSame('self_order', $order->source);
        $this->assertSame('pending', $order->status);
        $this->assertSame(56000, $order->total);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_name' => 'Caffe Latte', 'quantity' => 2, 'note' => 'less sugar']);

        $this->get(route('menu.order', $order))->assertOk()->assertSee($order->code)->assertSee('Rp56.000');
    }

    public function test_price_comes_from_database_not_request(): void
    {
        $this->post('/menu/pesan', [
            'customer_name' => 'Budi',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1, 'price' => 1]],
        ]);

        $this->assertSame(28000, Order::firstOrFail()->total);
    }

    public function test_unavailable_product_cannot_be_ordered(): void
    {
        $this->latte->update(['is_available' => false]);

        $this->post('/menu/pesan', [
            'customer_name' => 'Budi',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
    }

    public function test_name_and_items_are_required(): void
    {
        $this->post('/menu/pesan', ['items' => []])->assertSessionHasErrors(['customer_name', 'items']);
    }

    public function test_tv_display_is_public_and_lists_menu(): void
    {
        $this->latte->update(['is_featured' => true]);

        $this->get('/layar')->assertOk()->assertSee('Caffe Latte')->assertSee('28k')->assertSee('Scan &amp; pesan dari meja', false);
    }
}
