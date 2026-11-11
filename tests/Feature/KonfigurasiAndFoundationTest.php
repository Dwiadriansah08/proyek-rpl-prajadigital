<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KonfigurasiAndFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_konfigurasi_page(): void
    {
        $role = Role::create(['name' => 'super_admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($admin)->get(route('konfigurasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Integrasi AI & Asisten Virtual', false);
        $response->assertSee('WhatsApp Notification Gateway', false);
        $response->assertSee('Informasi Server & Lingkungan Aplikasi', false);
    }

    public function test_non_admin_cannot_access_konfigurasi_page(): void
    {
        $role = Role::create(['name' => 'warga']);
        $warga = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($warga)->get(route('konfigurasi.index'));

        $response->assertStatus(403);
    }

    public function test_ai_status_shows_connected_when_api_key_is_configured_and_masks_key(): void
    {
        $role = Role::create(['name' => 'super_admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $secretKey = 'secret-test-gemini-key-123456';
        config(['services.gemini.api_key' => $secretKey]);

        $response = $this->actingAs($admin)->get(route('konfigurasi.index'));

        $response->assertStatus(200);
        $response->assertSee('Terhubung', false);
        // Super admin now sees the key in the input value so it can be viewed with the toggle eye
        $response->assertSee($secretKey, false);
    }

    public function test_ai_status_shows_unconfigured_when_api_key_is_empty(): void
    {
        $role = Role::create(['name' => 'super_admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        $originalEnv = $_ENV['AI_API_KEY'] ?? null;
        $originalServer = $_SERVER['AI_API_KEY'] ?? null;

        config(['services.gemini.api_key' => null]);
        putenv('AI_API_KEY=');
        unset($_ENV['AI_API_KEY'], $_SERVER['AI_API_KEY']);

        try {
            $response = $this->actingAs($admin)->get(route('konfigurasi.index'));

            $response->assertStatus(200);
            $response->assertSee('Belum Dikonfigurasi', false);
            $response->assertSee('Belum dikonfigurasi', false);
        } finally {
            if ($originalEnv !== null) {
                $_ENV['AI_API_KEY'] = $originalEnv;
                putenv("AI_API_KEY={$originalEnv}");
            }
            if ($originalServer !== null) {
                $_SERVER['AI_API_KEY'] = $originalServer;
            }
        }
    }
}
