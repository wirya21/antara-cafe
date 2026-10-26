<?php

namespace Tests\Feature;

use App\Models\KafehubAdmin;
use App\Models\User;
use App\Support\LineIcon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cafe_subdomain_root_goes_to_menu(): void
    {
        $this->get('http://antara.kafehub.id/')->assertRedirect(route('menu'));
    }

    public function test_main_domain_shows_kafehub_landing_with_showcase(): void
    {
        $this->get('http://kafehub.id/')->assertOk()
            ->assertSee('Kafe Anda,')
            ->assertSee('Antara Cafe')
            ->assertSee('https://antara.kafehub.id/menu');

        $this->get('http://antara.kafehub.id/kafehub')->assertOk()->assertSee('Sudah berjalan di Antara Cafe');
    }

    public function test_join_button_uses_configured_whatsapp(): void
    {
        $this->get(route('kafehub'))->assertDontSee('wa.me');

        config(['kafehub.whatsapp' => '6281234567890']);

        $this->get(route('kafehub'))->assertSee('https://wa.me/6281234567890', false)->assertSee('Gabungkan kafe saya');
    }

    public function test_main_domain_sends_other_pages_to_the_cafe_subdomain(): void
    {
        config(['app.url' => 'https://antara.kafehub.id']);

        $this->get('http://kafehub.id/menu?meja=3')->assertRedirect('https://antara.kafehub.id/menu?meja=3');
        $this->get('http://www.kafehub.id/admin')->assertRedirect('https://antara.kafehub.id/admin');
        $this->get('http://kafehub.id/kafehub')->assertRedirect('http://kafehub.id');
        $this->get('http://antara.kafehub.id/kafehub')->assertOk();
    }

    public function test_kafehub_manager_can_edit_landing_content(): void
    {
        $manager = KafehubAdmin::create(['name' => 'Pengelola', 'username' => 'kafehub', 'password' => 'rahasia123']);

        $this->get('http://kafehub.id/panel')->assertRedirect('http://kafehub.id/panel/masuk');
        $this->post('http://kafehub.id/panel/masuk', ['username' => 'kafehub', 'password' => 'salah'])->assertSessionHasErrors('username');
        $this->post('http://kafehub.id/panel/masuk', ['username' => 'kafehub', 'password' => 'rahasia123'])->assertRedirect('http://kafehub.id/panel');
        $this->assertAuthenticatedAs($manager, 'kafehub');

        $this->get('http://kafehub.id/panel')->assertOk()->assertSee('Pesan lewat QR');

        $this->put('http://kafehub.id/panel', [
            'whatsapp' => '0811 2222 333',
            'hero_title' => 'Kafe ramai,',
            'features' => [
                ['icon' => 'kopi', 'title' => 'Menu digital', 'text' => 'Foto dan harga selalu terbaru.'],
                ['icon' => 'bintang', 'title' => '', 'text' => ''],
            ],
            'faqs' => '',
        ])->assertSessionHasNoErrors()->assertRedirect('http://kafehub.id/panel');

        $this->get('http://kafehub.id/')->assertOk()
            ->assertSee('Kafe ramai,')
            ->assertSee('jalan lebih rapi.')
            ->assertSee('Menu digital')
            ->assertDontSee('Kasir cepat')
            ->assertDontSee('Tanya jawab')
            ->assertSee('https://wa.me/628112222333', false)
            ->assertSee(LineIcon::path('kopi'), false);

        $this->put('http://kafehub.id/panel/password', [
            'current_password' => 'rahasia123', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('baru12345', $manager->fresh()->password));
    }

    public function test_cafe_accounts_cannot_open_kafehub_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'kafehub', 'password' => 'rahasia123']);

        // Akun admin kafe tidak berlaku di panel kafehub, walau username dan password-nya sama.
        $this->post('http://kafehub.id/panel/masuk', ['username' => 'kafehub', 'password' => 'rahasia123'])->assertSessionHasErrors('username');
        $this->actingAs($admin)->get('http://kafehub.id/panel')->assertRedirect('http://kafehub.id/panel/masuk');
        $this->put('http://kafehub.id/panel', ['hero_title' => 'x'])->assertRedirect('http://kafehub.id/panel/masuk');
    }

    public function test_panel_lives_only_on_main_domain(): void
    {
        $this->get('http://antara.kafehub.id/panel')->assertRedirect('http://kafehub.id/panel');
        $this->get('http://antara.kafehub.id/admin/kafehub')->assertNotFound();
    }

    public function test_command_creates_panel_account(): void
    {
        $this->artisan('kafehub:akun')->assertSuccessful();
        $this->assertSame(1, KafehubAdmin::where('username', 'kafehub')->count());
        $this->assertSame(0, User::where('username', 'kafehub')->count());
    }
}
