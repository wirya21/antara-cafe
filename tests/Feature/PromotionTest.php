<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PromotionTest extends TestCase
{
    use RefreshDatabase;

    private Product $latte;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
    }

    private function order(int $qty, array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/menu/pesan', ['customer_name' => 'Sari', 'payment_method' => 'tunai', 'items' => [['product_id' => $this->latte->id, 'quantity' => $qty]], ...$extra]);
    }

    public function test_admin_creates_voucher_with_banner(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create(['role' => 'admin']))->post('/admin/promo', [
            'title' => 'Diskon Gajian',
            'discount_type' => 'percent',
            'discount_value' => 20,
            'voucher_code' => 'gajian20',
            'image' => UploadedFile::fake()->image('banner.jpg', 1680, 720),
            'is_active' => '1',
        ])->assertRedirect(route('admin.promotions.index'));

        $promo = Promotion::firstOrFail();
        $this->assertSame('GAJIAN20', $promo->voucher_code);
        Storage::disk('public')->assertExists($promo->image_path);

        $this->get('/menu')->assertSee('Diskon Gajian');
        $this->get('/layar')->assertSee('Diskon Gajian');
    }

    public function test_voucher_discount_is_applied(): void
    {
        Promotion::create(['title' => 'Gajian', 'discount_type' => 'percent', 'discount_value' => 20, 'voucher_code' => 'GAJIAN20']);

        $this->getJson('/menu/voucher?code=gajian20')->assertOk()->assertJsonPath('value', 20);
        $this->getJson('/menu/voucher?code=SALAH')->assertNotFound();

        $this->order(2, ['voucher_code' => 'gajian20'])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(56000, $order->subtotal);
        $this->assertSame(11200, $order->discount);
        $this->assertSame(44800, $order->total);
    }

    public function test_invalid_or_expired_voucher_is_rejected(): void
    {
        Promotion::create(['title' => 'Lama', 'discount_type' => 'fixed', 'discount_value' => 5000, 'voucher_code' => 'LAMA', 'ends_at' => now()->subDay()]);

        $this->order(1, ['voucher_code' => 'LAMA'])->assertSessionHasErrors('voucher_code');
        $this->assertSame(0, Order::count());
    }

    public function test_voucher_minimum_purchase(): void
    {
        Promotion::create(['title' => 'Min', 'discount_type' => 'fixed', 'discount_value' => 10000, 'voucher_code' => 'HEMAT', 'min_purchase' => 50000]);

        $this->order(1, ['voucher_code' => 'HEMAT'])->assertSessionHasErrors('voucher_code');
        $this->order(2, ['voucher_code' => 'HEMAT'])->assertRedirect();
        $this->assertSame(46000, Order::firstOrFail()->total);
    }

    public function test_best_automatic_promo_applies_without_code(): void
    {
        Promotion::create(['title' => 'Kecil', 'discount_type' => 'fixed', 'discount_value' => 2000]);
        Promotion::create(['title' => 'Besar', 'discount_type' => 'percent', 'discount_value' => 10]);
        Promotion::create(['title' => 'Nonaktif', 'discount_type' => 'fixed', 'discount_value' => 20000, 'is_active' => false]);

        $this->order(1);

        $order = Order::firstOrFail();
        $this->assertSame(2800, $order->discount);
        $this->assertSame('Besar', $order->promotion->title);
    }

    public function test_member_registers_and_earns_points_when_paid(): void
    {
        $this->order(2, ['member_phone' => '+62 812-3456-7890']);

        $member = Member::firstOrFail();
        $this->assertSame('081234567890', $member->phone);
        $this->assertSame('Sari', $member->name);
        $this->assertSame(0, $member->points);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('cashier.pay', Order::first()), ['payment_method' => 'tunai']);
        $this->assertSame(5, $member->fresh()->points); // Rp56.000

        $this->post(route('admin.orders.void', Order::first()), ['reason' => 'Tes']);
        $this->assertSame(0, $member->fresh()->points);

        $this->get('/admin/member')->assertOk()->assertSee('081234567890');
    }

    public function test_cashier_order_with_existing_member(): void
    {
        $member = Member::create(['name' => 'Budi', 'phone' => '081111111111', 'points' => 3]);

        $this->actingAs(User::factory()->create(['role' => 'kasir']))->post('/kasir/pesanan', [
            'payment_method' => 'qris',
            'member_phone' => '0811-1111-1111',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 4]],
        ]);

        $this->assertSame(14, $member->fresh()->points);
        $this->assertTrue(Order::first()->member->is($member));
        $this->get(route('cashier.receipt', Order::first()))->assertSee('Poin: +11');
    }

    public function test_popup_promo_shows_on_menu_only_when_flagged_and_running(): void
    {
        $this->get('/menu')->assertOk()->assertDontSee('Kode voucher</p>', false);

        Promotion::create(['title' => 'Gajian Ceria', 'discount_type' => 'percent', 'discount_value' => 20, 'voucher_code' => 'GAJIAN', 'is_active' => true, 'show_popup' => false]);
        $this->get('/menu')->assertDontSee('promo-title', false);

        $promo = Promotion::create(['title' => 'Happy Hour', 'description' => 'Semua kopi lebih hemat', 'discount_type' => 'percent', 'discount_value' => 15, 'voucher_code' => 'HAPPY', 'is_active' => true, 'show_popup' => true]);
        $this->get('/menu')->assertSee('promo-title', false)->assertSee('Happy Hour')->assertSee('HAPPY')->assertSee('Diskon 15%');

        $promo->update(['ends_at' => now()->subDay()]);
        $this->get('/menu')->assertDontSee('promo-title', false);
    }

    public function test_admin_can_flag_promo_as_popup(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post(route('admin.promotions.store'), [
            'title' => 'Buka Baru',
            'is_active' => '1',
            'show_popup' => '1',
        ])->assertRedirect(route('admin.promotions.index'));

        $this->assertTrue(Promotion::firstOrFail()->show_popup);
    }
}
