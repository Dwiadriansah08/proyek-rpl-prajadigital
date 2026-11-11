<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrajaDigitalRevisionsTest extends TestCase
{
    use RefreshDatabase;

    private Role $roleSuperAdmin;

    private Role $roleWarga;

    private User $superAdmin;

    private User $wargaUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleSuperAdmin = Role::create(['name' => 'super_admin']);
        $this->roleWarga = Role::create(['name' => 'warga']);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@prajadigital.test',
            'username' => 'superadmin',
            'password' => bcrypt('password123'),
            'role_id' => $this->roleSuperAdmin->id,
            'is_active' => true,
        ]);

        $this->wargaUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@prajadigital.test',
            'username' => 'budisantoso',
            'password' => bcrypt('password123'),
            'role_id' => $this->roleWarga->id,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Verifikasi Halaman Login Clean & Minimalis
     */
    public function test_login_page_is_clean_minimalist_and_contains_required_elements_only(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // Branding PrajaDigital
        $response->assertSee('PrajaDigital');

        // Input Email atau Username
        $response->assertSee('name="email"', false);
        $response->assertSee('Email atau Username');

        // Input Password dengan ikon toggle mata
        $response->assertSee('name="password"', false);
        $response->assertSee('togglePasswordBtn', false);
        $response->assertSee('bi-eye', false);

        // Tautan Lupa Password? mengarah ke route password.request yang nyata
        $response->assertSee(route('password.request'), false);
        $response->assertSee('Lupa Password?');

        // Tombol Masuk
        $response->assertSee('Masuk');

        // Checkbox Ingat Saya
        $response->assertSee('name="remember"', false);
        $response->assertSee('Ingat Saya');
    }

    /**
     * 1b. Verifikasi Autentikasi Login Mendukung Email dan Username
     */
    public function test_user_can_login_with_email_and_with_username(): void
    {
        // Login dengan Email
        $resEmail = $this->post('/login', [
            'email' => 'budi@prajadigital.test',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($this->wargaUser);
        $resEmail->assertRedirect(route('dashboard', absolute: false));

        $this->post('/logout');
        $this->assertGuest();

        // Login dengan Username
        $resUsername = $this->post('/login', [
            'email' => 'budisantoso',
            'password' => 'password123',
        ]);
        $this->assertAuthenticatedAs($this->wargaUser);
        $resUsername->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * 2. Verifikasi Kartu AI Assistant Dihapus dari Dashboard Warga & Chatbot Mengambang Tetap Ada
     */
    public function test_warga_dashboard_does_not_contain_ai_card_but_keeps_floating_chatbot(): void
    {
        $response = $this->actingAs($this->wargaUser)->get('/dashboard');

        $response->assertStatus(200);

        // Kartu pintasan cepat warga tetap ada
        $response->assertSee('Ajukan Surat');
        $response->assertSee('Arsip Saya');
        $response->assertSee('Kabar Desa');

        // Kartu AI Assistant di dashboard warga telah hilang
        $response->assertDontSee('Tanya regulasi &amp; syarat', false);
        $response->assertDontSee('Tanya regulasi & syarat', false);

        // Chatbot mengambang tetap tampil di layout
        $response->assertSee('chatbotFab', false);
        $response->assertSee('chatbotPanel', false);
    }

    /**
     * 3. Verifikasi Kolom Username di Manajemen User dan Pencarian Username
     */
    public function test_user_management_displays_username_column_and_supports_username_search(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));

        $response->assertStatus(200);

        // Header kolom Username
        $response->assertSee('<th>Username</th>', false);
        $response->assertSee('superadmin');
        $response->assertSee('budisantoso');

        // Pencarian berdasarkan username
        $searchResponse = $this->actingAs($this->superAdmin)->get(route('users.index', ['search' => 'budisantoso']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertViewHas('users', function ($users) {
            return $users->contains('username', 'budisantoso') && ! $users->contains('username', 'superadmin');
        });

        // Pencarian berdasarkan nama tetap berfungsi
        $searchNameResponse = $this->actingAs($this->superAdmin)->get(route('users.index', ['search' => 'Super Administrator']));
        $searchNameResponse->assertStatus(200);
        $searchNameResponse->assertViewHas('users', function ($users) {
            return $users->contains('username', 'superadmin') && ! $users->contains('username', 'budisantoso');
        });

        // Pencarian berdasarkan email tetap berfungsi
        $searchEmailResponse = $this->actingAs($this->superAdmin)->get(route('users.index', ['search' => 'budi@prajadigital.test']));
        $searchEmailResponse->assertStatus(200);
        $searchEmailResponse->assertViewHas('users', function ($users) {
            return $users->contains('username', 'budisantoso') && ! $users->contains('username', 'superadmin');
        });
    }
}
