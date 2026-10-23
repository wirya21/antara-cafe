<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private Product $latte;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Http::fake(['api.fonnte.com/*' => Http::response(['status' => true])]);
        Setting::put('qris_image_path', UploadedFile::fake()->image('qris.png')->store('pengaturan', 'public'));
        Setting::put('bank_accounts', 'BCA 1234567890 a.n. Antara Cafe');

        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
    }

    private function placeOrder(string $method): Order
    {
        $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'payment_method' => $method,
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1]],
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_customer_must_choose_a_payment_method(): void
    {
        $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('payment_method');

        $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'payment_method' => 'kartu',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('payment_method');
    }

    public function test_unconfigured_methods_are_hidden_and_rejected(): void
    {
        Setting::put('qris_image_path', null);
        Setting::put('bank_accounts', null);

        $this->get('/menu')->assertOk()->assertSee('Bayar di kasir')->assertDontSee('Scan &amp; bayar', false);

        $this->post('/menu/pesan', [
            'customer_name' => 'Adi',
            'payment_method' => 'qris',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1]],
        ])->assertSessionHasErrors('payment_method');
    }

    public function test_cash_order_waits_for_cashier_and_shows_code(): void
    {
        $order = $this->placeOrder('tunai');

        $this->assertSame('pending', $order->status);
        $this->assertSame('tunai', $order->payment_method);
        $this->get(route('menu.order', $order))->assertOk()
            ->assertSee('bayar tunai di kasir')
            ->assertDontSee('Kirim bukti pembayaran');
    }

    public function test_qris_order_shows_qris_and_accepts_proof(): void
    {
        $order = $this->placeOrder('qris');

        $this->get(route('menu.order', $order))->assertOk()
            ->assertSee('QRIS Antara Cafe')
            ->assertSee('Kirim bukti pembayaran');

        $this->post(route('menu.order.proof', $order), ['proof' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect(route('menu.order', $order));

        $order->refresh();
        $this->assertNotNull($order->payment_proof_path);
        Storage::disk('local')->assertExists($order->payment_proof_path);
        $this->assertSame('pending', $order->status, 'Bukti saja tidak boleh membuat pesanan lunas.');
    }

    public function test_proof_is_private_to_staff(): void
    {
        $order = $this->placeOrder('transfer');
        $this->post(route('menu.order.proof', $order), ['proof' => UploadedFile::fake()->image('bukti.jpg')]);

        $this->get(route('cashier.proof', $order))->assertRedirect('/login');

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->get(route('cashier.proof', $order))->assertOk();
    }

    public function test_cash_order_does_not_accept_proof(): void
    {
        $order = $this->placeOrder('tunai');

        $this->post(route('menu.order.proof', $order), ['proof' => UploadedFile::fake()->image('bukti.jpg')])->assertStatus(409);
    }

    public function test_customer_can_switch_payment_method_while_pending(): void
    {
        $order = $this->placeOrder('qris');

        $this->post(route('menu.order.method', $order), ['payment_method' => 'tunai'])->assertRedirect();
        $this->assertSame('tunai', $order->fresh()->payment_method);
    }

    public function test_admin_confirms_payment_then_completes_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->placeOrder('transfer');

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Konfirmasi pembayaran')
            ->assertSee('Tolak / batalkan pesanan');

        $this->post(route('cashier.pay', $order), ['payment_method' => 'transfer', 'kembali' => 'admin'])
            ->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame('processing', $order->stage());
        $this->get(route('menu.order', $order))->assertSee('sedang dibuat');

        $this->post(route('cashier.complete', $order), ['kembali' => 'admin'])->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame('completed', $order->fresh()->stage());
    }

    public function test_cancelled_order_cannot_be_paid(): void
    {
        $order = $this->placeOrder('tunai');
        $this->actingAs(User::factory()->create(['role' => 'kasir']));

        $this->post(route('cashier.cancel', $order))->assertRedirect(route('cashier.index'));
        $this->post(route('cashier.pay', $order), ['payment_method' => 'tunai'])->assertStatus(409);
    }

    public function test_unpaid_order_cannot_be_completed(): void
    {
        $order = $this->placeOrder('tunai');

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->post(route('cashier.complete', $order))->assertStatus(409);
    }

    public function test_alerts_endpoint_counts_pending_orders(): void
    {
        $order = $this->placeOrder('qris');
        $this->post(route('menu.order.proof', $order), ['proof' => UploadedFile::fake()->image('bukti.jpg')]);
        $this->placeOrder('tunai');

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->getJson(route('cashier.alerts'))
            ->assertOk()
            ->assertJson(['pending' => 2, 'proofs' => 1, 'latest' => Order::max('id')]);
    }

    public function test_whatsapp_is_sent_for_new_orders_when_configured(): void
    {
        Setting::put('fonnte_token', 'token-rahasia');
        Setting::put('whatsapp_numbers', '0812-3456-7890, 6281111111111');

        $order = $this->placeOrder('qris');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.fonnte.com/send'
            && $request->hasHeader('Authorization', 'token-rahasia')
            && $request['target'] === '6281234567890,6281111111111'
            && str_contains($request['message'], $order->code));
    }

    public function test_no_whatsapp_without_token(): void
    {
        Setting::put('whatsapp_numbers', '081234567890');

        $this->placeOrder('qris');

        Http::assertNothingSent();
    }

    public function test_admin_saves_payment_and_whatsapp_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'image' => UploadedFile::fake()->image('qris.png'),
            'bank_accounts' => 'BCA 123 a.n. Antara',
            'whatsapp_numbers' => '081234567890',
            'fonnte_token' => 'abc123',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('abc123', Setting::get('fonnte_token'));
        $this->assertNotSame('abc123', Setting::find('fonnte_token')->value, 'Token harus disimpan terenkripsi.');
        Storage::disk('public')->assertExists(Setting::get('qris_image_path'));

        // Token kosong saat menyimpan ulang tidak menghapus token lama.
        $this->put(route('admin.settings.update'), ['whatsapp_numbers' => '081234567890']);
        $this->assertSame('abc123', Setting::get('fonnte_token'));

        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Aktif')->assertDontSee('abc123');

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->get(route('admin.settings.edit'))->assertForbidden();
    }
}
