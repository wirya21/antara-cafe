<?php

namespace Tests\Feature;

use App\Models\Cafe;
use App\Models\Category;
use App\Models\KafehubAdmin;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenancyTest extends TestCase
{
    use RefreshDatabase;

    private function signInPanel(): void
    {
        $this->actingAs(KafehubAdmin::create(['name' => 'Pengelola', 'username' => 'kafehub', 'password' => 'rahasia123']), 'kafehub');
    }

    private function menuItem(string $name): Product
    {
        $category = Category::firstOrCreate(['slug' => 'kopi'], ['name' => 'Kopi', 'group' => 'kopi']);

        return Product::create(['category_id' => $category->id, 'name' => $name, 'slug' => str($name)->slug(), 'price' => 20000]);
    }

    public function test_superadmin_registers_a_cafe_and_its_admin_can_log_in(): void
    {
        $this->menuItem('Kopi Antara');
        $this->signInPanel();

        $response = $this->post('http://kafehub.id/panel/kafe', [
            'name' => 'Kopi Senja', 'slug' => 'senja', 'owner_name' => 'Budi', 'owner_phone' => '08123456789',
            'admin_username' => 'admin', 'active_until' => now()->addDays(30)->toDateString(), 'is_trial' => '1', 'is_active' => '1',
        ]);

        $cafe = Cafe::findBySlug('senja');
        $response->assertRedirect('http://kafehub.id/panel/kafe/'.$cafe->id)->assertSessionHas('credentials');
        $this->assertSame('trial', $cafe->status());
        $password = session('credentials.password');
        $this->app['auth']->shouldUse('web');

        // Admin kafe baru masuk lewat subdomainnya sendiri dan tidak melihat menu Antara.
        $this->post('http://senja.kafehub.id/login', ['username' => 'admin', 'password' => $password])->assertRedirect();
        $this->assertAuthenticated();
        $this->assertSame($cafe->id, auth()->user()->cafe_id);
        $this->get('http://senja.kafehub.id/admin/products')->assertOk()->assertDontSee('Kopi Antara');
        $this->get('http://senja.kafehub.id/menu')->assertOk()->assertSee('Kopi Senja')->assertDontSee('Kopi Antara');
    }

    public function test_accounts_and_data_stay_inside_their_cafe(): void
    {
        User::factory()->create(['username' => 'admin', 'password' => 'antara123', 'role' => 'admin']);
        $this->menuItem('Kopi Antara');

        $other = Cafe::create(['name' => 'Kopi Senja', 'slug' => 'senja']);
        Tenant::run($other, function () {
            User::factory()->create(['username' => 'admin', 'password' => 'senja1234', 'role' => 'admin']);
            $this->menuItem('Es Senja');
            Setting::put('bank_accounts', 'BCA Senja');
        });

        $this->get('http://antara.kafehub.id/menu')->assertSee('Kopi Antara')->assertDontSee('Es Senja');
        $this->get('http://senja.kafehub.id/menu')->assertSee('Es Senja')->assertDontSee('Kopi Antara');
        $this->assertNull(Tenant::run($this->cafe, fn () => Setting::get('bank_accounts')));
        $this->assertSame('BCA Senja', Tenant::run($other, fn () => Setting::get('bank_accounts')));

        // Password akun Antara tidak berlaku di kafe lain walau username-nya sama.
        $this->post('http://senja.kafehub.id/login', ['username' => 'admin', 'password' => 'antara123'])->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_unknown_and_inactive_cafes_are_closed(): void
    {
        $this->get('http://belumada.kafehub.id/menu')->assertNotFound()->assertSee('Kafe belum terdaftar');

        Cafe::create(['name' => 'Kopi Libur', 'slug' => 'libur', 'is_active' => false]);
        $this->get('http://libur.kafehub.id/menu')->assertStatus(503)->assertSee('Kopi Libur sedang tidak aktif');

        Cafe::create(['name' => 'Kopi Lewat', 'slug' => 'lewat', 'active_until' => now()->subDay()]);
        $this->get('http://lewat.kafehub.id/login')->assertStatus(503);
    }

    public function test_subdomain_rules(): void
    {
        $this->signInPanel();
        $base = ['name' => 'Kafe', 'admin_username' => 'admin', 'is_active' => '1'];

        $this->post('http://kafehub.id/panel/kafe', $base + ['slug' => 'antara'])->assertSessionHasErrors('slug');
        $this->post('http://kafehub.id/panel/kafe', $base + ['slug' => 'www'])->assertSessionHasErrors('slug');
        $this->post('http://kafehub.id/panel/kafe', $base + ['slug' => 'Kopi Enak'])->assertSessionHasErrors('slug');
        $this->post('http://kafehub.id/panel/kafe', $base + ['slug' => 'KOPI-enak'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNotNull(Cafe::findBySlug('kopi-enak'));
    }

    public function test_superadmin_extends_subscription_and_resets_password(): void
    {
        $this->signInPanel();
        $cafe = Cafe::create(['name' => 'Kopi Senja', 'slug' => 'senja', 'is_trial' => true, 'active_until' => today()->addDays(3)]);
        $user = Tenant::run($cafe, fn () => User::factory()->create(['username' => 'admin', 'role' => 'admin']));

        $this->post("http://kafehub.id/panel/kafe/{$cafe->id}/perpanjang")->assertRedirect();
        $cafe->refresh();
        $this->assertFalse($cafe->is_trial);
        $this->assertTrue($cafe->active_until->isSameDay(today()->addDays(3)->addMonthNoOverflow()));

        $this->post("http://kafehub.id/panel/kafe/{$cafe->id}/akun/{$user->id}/password")->assertSessionHas('credentials');
        $this->assertTrue(password_verify(session('credentials.password'), $user->fresh()->password));
    }

    public function test_dashboard_summarises_all_cafes(): void
    {
        $this->signInPanel();
        Cafe::create(['name' => 'Kopi Senja', 'slug' => 'senja', 'is_trial' => true, 'active_until' => today()->addDays(5)]);

        $this->get('http://kafehub.id/panel')->assertOk()
            ->assertSee('Ringkasan')->assertSee('Antara Cafe')->assertSee('Kopi Senja')->assertSee('5 hari lagi');
        $this->get('http://kafehub.id/panel/kafe?status=trial')->assertOk()->assertSee('Kopi Senja')->assertDontSee('antara.kafehub.id');
    }
}
