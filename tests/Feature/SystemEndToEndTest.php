<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SystemEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private Desa $desa;

    private Rw $rw;

    private Rt $rt;

    private Role $roleWarga;

    private Role $roleRt;

    private Role $roleRw;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleWarga = Role::create(['name' => 'warga']);
        $this->roleRt = Role::create(['name' => 'rt']);
        $this->roleRw = Role::create(['name' => 'rw']);

        $this->desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Provinsi Gemilang',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);

        $this->rw = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '001',
            'ketua_rw' => 'Ketua RW 1',
            'alamat' => 'Lingkungan RW 1',
            'status' => true,
        ]);

        $this->rt = Rt::create([
            'rw_id' => $this->rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'status' => true,
        ]);
    }

    public function test_system_journey_resident_registration_and_authentication(): void
    {
        // 1. Register new resident
        $regResponse = $this->post('/register', [
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '3201015555550001',
            'nomor_kk' => '3201015555550002',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $regResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        // 2. Verify warga profile automatically created
        $this->assertDatabaseHas('warga', [
            'nik' => '3201015555550001',
            'email' => 'siti@example.com',
            'nama_lengkap' => 'Siti Aminah',
        ]);

        // 3. Logout
        $logoutResponse = $this->post('/logout');
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        // 4. Login back
        $loginResponse = $this->post('/login', [
            'email' => 'siti@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_system_journey_ai_chat_interaction_flow(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Halo, untuk membuat surat pengantar domisili silakan ajukan melalui menu Layanan Surat.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create([
            'role_id' => $this->roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        // Kirim pesan pertama ke AI Chat (akan membuat percakapan otomatis)
        $response = $this->actingAs($user)->postJson('/ai-chat', [
            'message' => 'Halo, bagaimana cara membuat surat pengantar domisili?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'conversation_id',
            'conversation_title',
            'user_message' => ['id', 'role', 'content'],
            'ai_message' => ['id', 'role', 'content', 'html'],
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertDatabaseHas('ai_conversations', ['user_id' => $user->id]);
        $this->assertDatabaseHas('ai_messages', [
            'role' => 'user',
            'content' => 'Halo, bagaimana cara membuat surat pengantar domisili?',
        ]);
        $this->assertDatabaseHas('ai_messages', [
            'role' => 'assistant',
            'content' => 'Halo, untuk membuat surat pengantar domisili silakan ajukan melalui menu Layanan Surat.',
        ]);
    }
}
