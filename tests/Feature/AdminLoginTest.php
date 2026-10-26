<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Connexion admin par l'adresse /login (aucun lien « Admin » sur le site public).
 */
class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_site_shows_no_admin_link(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('Admin</a>', false)->assertDontSee(route('admin.dashboard') . '"', false);
    }

    public function test_admin_can_log_in_from_login_url(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('secret-123')]);

        $this->get('/login')->assertOk()->assertSee('name="password"', false);

        $this->post(route('auth.login.post'), ['email' => $admin->email, 'password' => 'secret-123'])->assertRedirect();
        $this->assertAuthenticatedAs($admin);

        // Déjà connecté : /login mène au tableau de bord
        $this->get('/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_is_refused(): void
    {
        $admin = User::factory()->create(['password' => bcrypt('secret-123')]);

        $this->from('/login')->post(route('auth.login.post'), ['email' => $admin->email, 'password' => 'mauvais'])->assertSessionHasErrors();
        $this->assertGuest();
    }
}
