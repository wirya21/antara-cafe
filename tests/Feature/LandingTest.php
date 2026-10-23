<?php

namespace Tests\Feature;

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

        $this->get(route('kafehub'))->assertOk()->assertSee('Sudah berjalan di Antara Cafe');
    }

    public function test_join_button_uses_configured_whatsapp(): void
    {
        $this->get(route('kafehub'))->assertDontSee('wa.me');

        config(['kafehub.whatsapp' => '6281234567890']);

        $this->get(route('kafehub'))->assertSee('https://wa.me/6281234567890', false)->assertSee('Gabungkan kafe saya');
    }
}
