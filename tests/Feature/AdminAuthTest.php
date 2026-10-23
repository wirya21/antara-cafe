<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_can_log_in_and_see_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'rahasia123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk()->assertSee('Pendapatan hari ini');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'rahasia123']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'salah'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'rahasia123']);

        foreach (range(1, 5) as $_) {
            $this->post('/login', ['email' => $admin->email, 'password' => 'salah']);
        }

        $this->post('/login', ['email' => $admin->email, 'password' => 'rahasia123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_cashier_cannot_open_admin_panel(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']))
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
