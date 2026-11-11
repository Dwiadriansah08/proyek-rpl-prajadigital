<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use App\Services\AiChatContextService;
use App\Services\VillageStatistikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSyncAndChatbotLiveContextTest extends TestCase
{
    use RefreshDatabase;

    protected Desa $desa;

    protected Role $adminRole;

    protected Role $rwRole;

    protected Role $rtRole;

    protected Role $wargaRole;

    protected Rw $rw;

    protected Rt $rt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kode_desa' => '3201012001',
            'kecamatan' => 'Sukasari',
            'kabupaten_kota' => 'Bogor',
            'provinsi' => 'Jawa Barat',
            'alamat' => 'Jl. Sukamaju No. 1',
            'kepala_desa' => 'Bapak Kepala Desa',
        ]);

        $this->adminRole = Role::create(['name' => 'super_admin']);
        $this->rwRole = Role::create(['name' => 'rw']);
        $this->rtRole = Role::create(['name' => 'rt']);
        $this->wargaRole = Role::create(['name' => 'warga']);

        $this->rw = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '01',
            'ketua_rw' => 'SUJONO LAMA',
        ]);

        $this->rt = Rt::create([
            'rw_id' => $this->rw->id,
            'nomor_rt' => '01',
            'ketua_rt' => 'PAIDI LAMA',
        ]);
    }

    public function test_user_creation_with_warga_role_automatically_creates_warga_record_and_updates_stats(): void
    {
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'username' => 'budisantoso',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role_id' => $this->wargaRole->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'jenis_kelamin' => 'Laki-laki',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.index'));

        // Pastikan tabel users dan warga tersinkron
        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);

        $warga = Warga::where('email', $user->email)->first();
        $this->assertNotNull($warga);
        $this->assertEquals('Budi Santoso', $warga->nama_lengkap);
        $this->assertEquals('Kepala Keluarga', $warga->status_dalam_keluarga);
        $this->assertEquals('Laki-laki', $warga->jenis_kelamin);

        // Pastikan statistik kependudukan dashboard bertambah
        $statsService = app(VillageStatistikService::class);
        $pendudukStats = $statsService->getPendudukStats();
        $this->assertEquals(1, $pendudukStats['total_warga']);
        $this->assertEquals(1, $pendudukStats['laki_laki']);

        $kkStats = $statsService->getKkStats();
        $this->assertEquals(1, $kkStats['total_kepala_keluarga']);
    }

    public function test_user_deletion_automatically_removes_linked_warga_and_updates_stats(): void
    {
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);

        // Buat user warga
        $user = User::factory()->create([
            'name' => 'Warga Dihapus',
            'email' => 'hapus@example.com',
            'role_id' => $this->wargaRole->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $warga = Warga::create([
            'email' => $user->email,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'nama_lengkap' => $user->name,
            'nik' => '3201019999990001',
            'nomor_kk' => '3201019999990002',
            'jenis_kelamin' => 'Laki-laki',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_aktif' => 1,
        ]);

        $statsService = app(VillageStatistikService::class);
        VillageStatistikService::clearStatsCache();
        $this->assertEquals(1, $statsService->getPendudukStats()['total_warga']);

        // Hapus user melalui UserController
        $response = $this->actingAs($admin)->delete(route('users.destroy', $user->id));
        $response->assertRedirect(route('users.index'));

        // Cek bahwa user dan warga sama-sama terhapus
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('warga', ['id' => $warga->id]);

        // Statistik harus langsung berkurang menjadi 0
        $this->assertEquals(0, $statsService->getPendudukStats()['total_warga']);
    }

    public function test_chatbot_context_service_reads_live_active_users_for_ketua_wilayah(): void
    {
        // Daftarkan akun aktif baru untuk Ketua RW bernama "Rusdi Active"
        $userRw = User::factory()->create([
            'name' => 'Pak Rusdi Active',
            'email' => 'rusdi@desa.id',
            'nomor_telepon' => '081234567890',
            'role_id' => $this->rwRole->id,
            'rw_id' => $this->rw->id,
            'is_active' => true,
        ]);

        // Daftarkan akun aktif baru untuk Ketua RT bernama "Yono Active"
        $userRt = User::factory()->create([
            'name' => 'Pak Yono Active',
            'email' => 'yono@desa.id',
            'nomor_telepon' => '089876543210',
            'role_id' => $this->rtRole->id,
            'rt_id' => $this->rt->id,
            'is_active' => true,
        ]);

        $service = app(AiChatContextService::class);

        // Ambil data ketua wilayah untuk Super Admin
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);
        $ketuaData = $service->getKetuaWilayahData($admin, 'super_admin', 'ketua_wilayah');

        $this->assertNotNull($ketuaData);
        $this->assertEquals('Bapak Kepala Desa', $ketuaData['kepala_desa']);
        $this->assertEquals('Pak Rusdi Active', $ketuaData['rws'][0]['ketua_rw']);
        $this->assertEquals('081234567890', $ketuaData['rws'][0]['kontak']);
        $this->assertEquals('Pak Yono Active', $ketuaData['rws'][0]['rts'][0]['ketua_rt']);
        $this->assertEquals('089876543210', $ketuaData['rws'][0]['rts'][0]['kontak']);
    }

    public function test_super_admin_receives_live_master_snapshot_unconditionally(): void
    {
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);
        $service = app(AiChatContextService::class);

        // Pertanyaan acak / salam tanpa intent spesifik
        $context = $service->getContextForMessage($admin, 'Halo selamat pagi bot');

        $this->assertArrayHasKey('live_master_snapshot', $context);
        $this->assertNotNull($context['live_master_snapshot']);
        $this->assertEquals(0, $context['live_master_snapshot']['total_warga']);
        $this->assertArrayHasKey('users_per_role', $context['live_master_snapshot']);

        $formattedPrompt = $service->formatContextForSystemPrompt($context);
        $this->assertStringContainsString('LIVE MASTER SNAPSHOT DESA', $formattedPrompt);
    }

    public function test_dynamic_keyword_lookup_finds_specific_officials(): void
    {
        User::factory()->create([
            'name' => 'Bapak Rusdi Wijaya',
            'email' => 'rusdi.w@desa.id',
            'role_id' => $this->rwRole->id,
            'rw_id' => $this->rw->id,
            'is_active' => true,
        ]);

        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);
        $service = app(AiChatContextService::class);

        $lookup = $service->dynamicKeywordLookup('Apakah ada nama Rusdi di pengurus?', $admin, 'super_admin');

        $this->assertNotNull($lookup);
        $this->assertGreaterThanOrEqual(1, $lookup['total_found']);
        $this->assertEquals('Bapak Rusdi Wijaya', $lookup['items'][0]['nama']);
    }
}
