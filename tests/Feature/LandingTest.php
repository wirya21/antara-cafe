<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LineIcon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_admin_can_edit_landing_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.kafehub.edit'))->assertOk()->assertSee('Pesan lewat QR');

        $this->put(route('admin.kafehub.update'), [
            'whatsapp' => '0811 2222 333',
            'hero_title' => 'Kafe ramai,',
            'features' => [
                ['icon' => 'kopi', 'title' => 'Menu digital', 'text' => 'Foto dan harga selalu terbaru.'],
                ['icon' => 'bintang', 'title' => '', 'text' => ''],
            ],
            'faqs' => '',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.kafehub.edit'));

        $this->get('http://antara.kafehub.id/kafehub')->assertOk()
            ->assertSee('Kafe ramai,')
            ->assertSee('jalan lebih rapi.')
            ->assertSee('Menu digital')
            ->assertDontSee('Kasir cepat')
            ->assertDontSee('Tanya jawab')
            ->assertSee('https://wa.me/628112222333', false)
            ->assertSee(LineIcon::path('kopi'), false);
    }

    public function test_kasir_cannot_edit_landing(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->put(route('admin.kafehub.update'), ['hero_title' => 'x'])->assertForbidden();
    }
}
