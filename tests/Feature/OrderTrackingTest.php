<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Product $latte;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
        $this->cashier = User::factory()->create(['role' => 'kasir']);
    }

    private function selfOrder(): Order
    {
        $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'table_number' => '4',
            'payment_method' => 'tunai',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 2]],
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_order_page_shows_progress_timer_and_alert_button(): void
    {
        $order = $this->selfOrder();

        $this->get(route('menu.order', $order))->assertOk()
            ->assertSeeInOrder(['Bayar', 'Dibuat', 'Siap'])
            ->assertSee('sejak memesan')
            ->assertSee('Kabari saya saat siap')
            ->assertSee('orderTracker', false);
    }

    public function test_cashier_sees_wait_time_and_swipe_to_mark_ready(): void
    {
        $order = $this->selfOrder();
        $this->actingAs($this->cashier);

        $pending = $this->getJson(route('cashier.pending'))->assertOk()->json('pending.0');
        $this->assertSame($order->created_at->toIso8601String(), $pending['since']);

        $this->post(route('cashier.pay', $order), ['payment_method' => 'tunai']);
        $processing = $this->getJson(route('cashier.pending'))->json('processing.0');
        $this->assertSame($order->fresh()->paid_at->toIso8601String(), $processing['since']);

        $this->get(route('cashier.index'))->assertOk()->assertSee('Geser jika pesanan siap');

        $this->post(route('cashier.complete', $order))->assertRedirect(route('cashier.index'));
        $this->assertSame('completed', $order->fresh()->stage());
        $this->get(route('menu.order', $order))->assertSee('Pesanan siap!')->assertSee('meja 4');
    }

    public function test_marking_ready_twice_keeps_the_first_time(): void
    {
        $order = $this->selfOrder();
        $this->actingAs($this->cashier)->post(route('cashier.pay', $order), ['payment_method' => 'tunai']);
        $this->post(route('cashier.complete', $order));
        $first = $order->fresh()->completed_at;

        $this->travel(5)->minutes();
        $this->post(route('cashier.complete', $order));

        $this->assertTrue($first->equalTo($order->fresh()->completed_at));
    }

    public function test_history_returns_only_known_codes_without_login(): void
    {
        $mine = $this->selfOrder();
        $this->selfOrder();

        $this->getJson(route('menu.history.data', ['kode' => $mine->code.',bukan-kode,Z-0000']))
            ->assertOk()
            ->assertJsonCount(1, 'orders')
            ->assertJsonPath('orders.0.code', $mine->code)
            ->assertJsonPath('orders.0.stage_label', 'Menunggu pembayaran')
            ->assertJsonPath('orders.0.items', '2× Caffe Latte');

        $this->get(route('menu.history'))->assertOk()->assertSee('Pesanan saya');
    }

    public function test_cashier_order_can_be_tracked_from_receipt_qr(): void
    {
        $this->actingAs($this->cashier)->post(route('cashier.store'), [
            'payment_method' => 'tunai',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1]],
        ])->assertRedirect();
        $order = Order::latest('id')->firstOrFail();

        $this->get(route('cashier.receipt', $order))->assertOk()->assertSee(route('menu.order', $order));

        auth()->logout();
        $this->get(route('menu.order', $order))->assertOk()->assertSee('Pesanan sedang dibuat');
        $this->getJson(route('menu.order.status', $order))->assertJson(['stage' => 'processing']);
    }

    public function test_unpaid_cashier_orders_are_not_public(): void
    {
        $order = $this->selfOrder();
        $order->update(['source' => 'kasir']);

        $this->get(route('menu.order', $order))->assertNotFound();
    }
}
