<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    private Product $latte;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create(['role' => 'kasir', 'password' => 'rahasia123']);
        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
    }

    public function test_cashier_lands_on_cashier_page_after_login(): void
    {
        $this->post('/login', ['username' => $this->cashier->username, 'password' => 'rahasia123'])
            ->assertRedirect(route('cashier.index'));

        $this->get('/kasir')->assertOk()->assertSee('Menunggu bayar');
    }

    public function test_guest_cannot_open_cashier(): void
    {
        $this->get('/kasir')->assertRedirect('/login');
    }

    public function test_cashier_can_record_a_paid_walk_in_order(): void
    {
        $this->actingAs($this->cashier)->post('/kasir/pesanan', [
            'payment_method' => 'qris',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 3]],
        ])->assertRedirect(route('cashier.index'));

        $order = Order::firstOrFail();
        $this->assertSame('kasir', $order->source);
        $this->assertSame('paid', $order->status);
        $this->assertSame('qris', $order->payment_method);
        $this->assertSame(84000, $order->total);
        $this->assertTrue($order->cashier->is($this->cashier));
        $this->assertNotNull($order->paid_at);

        $this->get(route('cashier.receipt', $order))->assertOk()->assertSee('84.000');
    }

    public function test_cashier_sees_and_pays_customer_order(): void
    {
        $this->post('/menu/pesan', ['customer_name' => 'Sari', 'payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $order = Order::firstOrFail();

        $this->actingAs($this->cashier)->getJson('/kasir/pesanan-masuk')
            ->assertOk()->assertJsonPath('pending.0.code', $order->code)->assertJsonPath('pending.0.customer_name', 'Sari');

        $this->post(route('cashier.pay', $order), ['payment_method' => 'tunai'])->assertRedirect(route('cashier.index'));

        $this->assertSame('paid', $order->fresh()->status);
        $this->getJson('/kasir/pesanan-masuk')->assertJsonCount(0, 'pending')->assertJsonPath('processing.0.code', $order->code);
        $this->get(route('menu.order', $order))->assertSee('Sedang dibuat');
    }

    public function test_paid_order_cannot_be_paid_or_cancelled_again(): void
    {
        $this->post('/menu/pesan', ['customer_name' => 'Sari', 'payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $order = Order::firstOrFail();

        $this->actingAs($this->cashier)->post(route('cashier.pay', $order), ['payment_method' => 'tunai']);

        $this->post(route('cashier.pay', $order), ['payment_method' => 'qris'])->assertStatus(409);
        $this->post(route('cashier.cancel', $order))->assertStatus(409);
        $this->assertSame('tunai', $order->fresh()->payment_method);
    }

    public function test_cashier_can_cancel_pending_order(): void
    {
        $this->post('/menu/pesan', ['customer_name' => 'Sari', 'payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => 1]]]);
        $order = Order::firstOrFail();

        $this->actingAs($this->cashier)->post(route('cashier.cancel', $order))->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_cashier_cannot_open_admin_but_admin_can_open_cashier(): void
    {
        $this->actingAs($this->cashier)->get('/admin/products')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/kasir')->assertOk();
    }

    public function test_admin_can_create_cashier_account(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post('/admin/users', [
            'name' => 'Kasir Pagi',
            'username' => 'kasirpagi',
            'role' => 'kasir',
            'password' => 'kasirpagi1',
            'password_confirmation' => 'kasirpagi1',
        ])->assertRedirect(route('admin.users.index'));

        $this->post('/logout');
        $this->post('/login', ['username' => 'kasirpagi', 'password' => 'kasirpagi1'])
            ->assertRedirect(route('cashier.index'));
    }
}
