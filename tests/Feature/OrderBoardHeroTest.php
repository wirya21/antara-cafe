<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\Photo;
use App\Support\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderBoardHeroTest extends TestCase
{
    use RefreshDatabase;

    private Product $latte;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Kopi', 'slug' => 'kopi', 'group' => 'kopi']);
        $this->latte = Product::create(['category_id' => $category->id, 'name' => 'Caffe Latte', 'slug' => 'caffe-latte', 'price' => 28000]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function selfOrder(string $name): Order
    {
        $this->post('/menu/pesan', [
            'customer_name' => $name,
            'payment_method' => 'tunai',
            'items' => [['product_id' => $this->latte->id, 'quantity' => 1, 'note' => 'less sugar']],
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_board_separates_incoming_processing_and_done_orders(): void
    {
        $incoming = $this->selfOrder('Adi');
        $making = $this->selfOrder('Budi');
        $this->actingAs($this->admin);

        $this->post(route('cashier.pay', $making), ['payment_method' => 'tunai', 'kembali' => 'pesanan', 'tab' => 'masuk'])
            ->assertRedirect(route('admin.board.index', ['tab' => 'masuk']));
        $this->get(route('admin.board.index'))->assertSee("Pembayaran {$making->code} dikonfirmasi");

        $this->get(route('admin.board.index'))->assertOk()
            ->assertSee($incoming->code)->assertDontSee($making->code)
            ->assertSee('less sugar')->assertSee('Terima');

        $this->get(route('admin.board.index', ['tab' => 'diproses']))->assertOk()
            ->assertSee($making->code)->assertDontSee($incoming->code)
            ->assertSee('Geser jika pesanan siap');

        $this->getJson(route('admin.board.counts'))->assertOk()
            ->assertJson(['masuk' => 1, 'diproses' => 1, 'selesai' => 0, 'batal' => 0]);

        $this->post(route('cashier.complete', $making), ['kembali' => 'pesanan', 'tab' => 'diproses'])
            ->assertRedirect(route('admin.board.index', ['tab' => 'diproses']));
        $this->post(route('cashier.cancel', $incoming), ['kembali' => 'pesanan']);

        $this->getJson(route('admin.board.counts'))
            ->assertJson(['masuk' => 0, 'diproses' => 0, 'selesai' => 1, 'batal' => 1]);
        $this->get(route('admin.board.index', ['tab' => 'selesai']))->assertSee($making->code);
    }

    public function test_cashier_role_is_not_sent_to_admin_board(): void
    {
        $order = $this->selfOrder('Adi');

        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->post(route('cashier.cancel', $order), ['kembali' => 'pesanan'])
            ->assertRedirect(route('cashier.index'));
        $this->get(route('admin.board.index'))->assertForbidden();
    }

    public function test_admin_nav_shows_active_order_badge(): void
    {
        $this->selfOrder('Adi');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertSeeInOrder(['Pesanan', '1', 'Transaksi']);
    }

    public function test_hero_image_and_text_can_be_changed(): void
    {
        Storage::fake('public');
        $this->get('/menu')->assertSee('Mau ngopi apa hari ini?')
            ->assertSee('Sesuaikan pesanan')->assertSee('Tambah minuman?');

        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'hero_image' => UploadedFile::fake()->image('kafe.jpg', 1600, 900),
            'hero_title' => 'Selamat pagi, Bali!',
            'hero_subtitle' => 'Kopi lokal disangrai sendiri.',
        ])->assertSessionHasNoErrors();

        $path = Setting::get('hero_image_path');
        Storage::disk('public')->assertExists($path);
        $this->get('/menu')->assertSee('Selamat pagi, Bali!')->assertSee('Kopi lokal disangrai sendiri.')->assertSee(basename($path));

        $this->put(route('admin.settings.update'), ['remove_hero_image' => '1'])->assertSessionHasNoErrors();
        $this->assertNull(Setting::get('hero_image_path'));
        Storage::disk('public')->assertMissing($path);
        $this->get('/menu')->assertSee('Mau ngopi apa hari ini?');
    }

    public function test_cafe_logo_can_be_uploaded_and_removed(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'logo' => UploadedFile::fake()->image('logo.png', 512, 512),
        ])->assertSessionHasNoErrors();

        $path = Tenant::cafe()->fresh()->logo_path;
        Storage::disk('public')->assertExists($path);
        $this->get('/menu')->assertSee(basename($path));
        $this->get('/kasir')->assertSee(basename($path));

        $this->put(route('admin.settings.update'), ['remove_logo' => '1'])->assertSessionHasNoErrors();
        $this->assertNull(Tenant::cafe()->fresh()->logo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_large_uploads_are_shrunk_for_the_web(): void
    {
        Storage::fake('public');

        $path = Photo::store(UploadedFile::fake()->image('besar.jpg', 3000, 2000), 'products', 900);

        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertSame([900, 600], [$width, $height]);
    }
}
