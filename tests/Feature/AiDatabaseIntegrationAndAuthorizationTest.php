<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Models\Warga;
use App\Services\AiChatContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature test untuk menguji integrasi database, otorisasi data backend,
 * role boundary (Warga, RT, RW, Super Admin), penanganan data kosong,
 * dan kegagalan provider AI.
 */
class AiDatabaseIntegrationAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Desa $desa;

    private Rw $rw1;

    private Rw $rw2;

    private Rt $rt1;

    private Rt $rt2;

    private Role $roleWarga;

    private Role $roleRt;

    private Role $roleRw;

    private Role $roleSuperAdmin;

    private User $userWarga1;

    private User $userWarga2;

    private User $userRt;

    private User $userRw;

    private User $userAdmin;

    private Warga $warga1;

    private Warga $warga2;

    private SuratTemplate $templateSku;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleWarga = Role::create(['name' => 'warga']);
        $this->roleRt = Role::create(['name' => 'rt']);
        $this->roleRw = Role::create(['name' => 'rw']);
        $this->roleSuperAdmin = Role::create(['name' => 'super_admin']);

        $this->desa = Desa::create([
            'nama' => 'Desa Sukamaju Mandiri',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Jawa Barat',
            'alamat' => 'Jl. Merdeka No. 45',
            'kepala_desa' => 'H. Ahmad Sobari',
            'kontak' => '081234567890',
        ]);

        $this->rw1 = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '001',
            'ketua_rw' => 'Ketua RW 001',
            'status' => true,
        ]);

        $this->rw2 = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '002',
            'ketua_rw' => 'Ketua RW 002',
            'status' => true,
        ]);

        $this->rt1 = Rt::create([
            'rw_id' => $this->rw1->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 001',
            'status' => true,
        ]);

        $this->rt2 = Rt::create([
            'rw_id' => $this->rw2->id,
            'nomor_rt' => '002',
            'ketua_rt' => 'Ketua RT 002',
            'status' => true,
        ]);

        $this->warga1 = Warga::create([
            'nik' => '3201010101010001',
            'nomor_kk' => '3201010101010099',
            'nama_lengkap' => 'Budi Santoso',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1990-05-12',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Karyawan Swasta',
            'email' => 'budi@warga.id',
            'alamat' => 'Jl. Melati No. 10',
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
        ]);

        $this->warga2 = Warga::create([
            'nik' => '3201010101010002',
            'nomor_kk' => '3201010101010098',
            'nama_lengkap' => 'Siti Aminah',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1992-08-20',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Ibu Rumah Tangga',
            'email' => 'siti@warga.id',
            'alamat' => 'Jl. Mawar No. 5',
            'rw_id' => $this->rw2->id,
            'rt_id' => $this->rt2->id,
            'status_dalam_keluarga' => 'Istri',
            'status_kependudukan' => 'Tetap',
        ]);

        $this->userWarga1 = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@warga.id',
            'role_id' => $this->roleWarga->id,
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
        ]);

        $this->userWarga2 = User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@warga.id',
            'role_id' => $this->roleWarga->id,
            'rw_id' => $this->rw2->id,
            'rt_id' => $this->rt2->id,
        ]);

        $this->userRt = User::factory()->create([
            'name' => 'Ketua RT 001',
            'email' => 'rt1@desa.id',
            'role_id' => $this->roleRt->id,
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
        ]);

        $this->userRw = User::factory()->create([
            'name' => 'Ketua RW 001',
            'email' => 'rw1@desa.id',
            'role_id' => $this->roleRw->id,
            'rw_id' => $this->rw1->id,
        ]);

        $this->userAdmin = User::factory()->create([
            'name' => 'Administrator Desa',
            'email' => 'admin@desa.id',
            'role_id' => $this->roleSuperAdmin->id,
        ]);

        $this->templateSku = SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Usaha (SKU)',
            'kode_surat' => 'SK-SKU',
            'deskripsi' => 'Surat Keterangan Usaha bagi UMKM',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Konteks service menghasilkan profil pengguna terverifikasi & wilayah resmi
     */
    public function test_context_service_builds_accurate_user_profile_and_wilayah(): void
    {
        $service = app(AiChatContextService::class);
        $context = $service->getContextForMessage($this->userWarga1, 'Halo, siapa saya?');

        $this->assertEquals('warga', $context['user_profile']['role']);
        $this->assertEquals('001', $context['user_profile']['rw_nomor']);
        $this->assertEquals('001', $context['user_profile']['rt_nomor']);
        $this->assertTrue($context['user_profile']['is_registered_warga']);
        $this->assertEquals($this->warga1->id, $context['user_profile']['warga_id']);
    }

    /**
     * Test 2: Otorisasi status pengajuan surat warga hanya menampilkan data miliknya sendiri
     */
    public function test_warga_only_receives_their_own_pengajuan_surat_status(): void
    {
        // Pengajuan untuk warga 1
        PengajuanSurat::create([
            'warga_id' => $this->warga1->id,
            'template_id' => $this->templateSku->id,
            'nomor_surat' => '470/01/SKU/2026',
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Kredit Usaha'],
        ]);

        // Pengajuan untuk warga 2
        PengajuanSurat::create([
            'warga_id' => $this->warga2->id,
            'template_id' => $this->templateSku->id,
            'nomor_surat' => '470/02/SKU/2026',
            'status' => 'SELESAI',
            'data_tambahan' => ['keperluan' => 'Toko Kelontong'],
        ]);

        // Request dari Warga 1
        $response = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Bagaimana status pengajuan surat saya?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Warga 1 melihat statusnya (MENUNGGU_RT) dan nomor miliknya
        $this->assertStringContainsString('Surat Keterangan Usaha (SKU)', $content);
        $this->assertStringContainsString('MENUNGGU_RT', $content);
        $this->assertStringContainsString('470/01/SKU/2026', $content);

        // Warga 1 TIDAK melihat nomor surat warga 2
        $this->assertStringNotContainsString('470/02/SKU/2026', $content);
    }

    /**
     * Test 3: Penanganan data kosong jika warga belum pernah mengajukan surat
     */
    public function test_empty_pengajuan_history_returns_factual_guidance(): void
    {
        // Warga 2 belum punya pengajuan surat
        $response = $this->actingAs($this->userWarga2)->postJson(route('ai.chat.store'), [
            'message' => 'Cek status surat saya',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        $this->assertStringContainsString('belum memiliki riwayat pengajuan surat', $content);
        $this->assertStringContainsString('Layanan Surat', $content);
    }

    /**
     * Test 4: Otorisasi role RT hanya melihat rekap data wilayah RT-nya
     */
    public function test_rt_role_receives_scoped_rekap_for_their_rt(): void
    {
        // Pengajuan warga 1 (RT 001 RW 001)
        PengajuanSurat::create([
            'warga_id' => $this->warga1->id,
            'template_id' => $this->templateSku->id,
            'status' => 'MENUNGGU_RT',
        ]);

        // Pengajuan warga 2 (RT 002 RW 002)
        PengajuanSurat::create([
            'warga_id' => $this->warga2->id,
            'template_id' => $this->templateSku->id,
            'status' => 'MENUNGGU_RW',
        ]);

        $service = app(AiChatContextService::class);
        $statusRt = $service->getPengajuanStatus($this->userRt, 'rt', 'status_pengajuan');

        $this->assertEquals('rt', $statusRt['role']);
        $this->assertTrue($statusRt['has_data']);
        $this->assertEquals(1, $statusRt['summary']['total']); // Hanya 1 surat di RT 1
    }

    /**
     * Test 5: Otorisasi role RW melihat rekap seluruh RT di bawah RW-nya
     */
    public function test_rw_role_receives_scoped_rekap_for_their_rw(): void
    {
        // Pengajuan warga 1 (RT 001 RW 001)
        PengajuanSurat::create([
            'warga_id' => $this->warga1->id,
            'template_id' => $this->templateSku->id,
            'status' => 'MENUNGGU_RW',
        ]);

        $service = app(AiChatContextService::class);
        $statusRw = $service->getPengajuanStatus($this->userRw, 'rw', 'status_pengajuan');

        $this->assertEquals('rw', $statusRw['role']);
        $this->assertEquals(1, $statusRw['summary']['total']);
    }

    /**
     * Test 6: Isolasi percakapan antar user terjaga ketat
     */
    public function test_user_cannot_access_or_modify_other_user_conversations(): void
    {
        $conv1 = AiConversation::create([
            'user_id' => $this->userWarga1->id,
            'title' => 'Chat Rahasia Budi',
        ]);

        // Warga 2 mencoba menghapus atau mengirim pesan ke percakapan Warga 1
        $deleteResponse = $this->actingAs($this->userWarga2)->deleteJson(route('ai.chat.destroy', $conv1->id));
        $deleteResponse->assertStatus(404);

        $postResponse = $this->actingAs($this->userWarga2)->postJson(route('ai.chat.store'), [
            'conversation_id' => $conv1->id,
            'message' => 'Mencoba injeksi ke chat orang lain',
        ]);
        $postResponse->assertStatus(404);
    }

    /**
     * Test 7: Kegagalan provider AI eksternal tertangani dengan fallback aman dan tidak fatal
     */
    public function test_provider_failure_falls_back_gracefully_without_hanging(): void
    {
        config(['app.ai_provider' => 'gemini']);
        putenv('AI_PROVIDER=gemini');
        putenv('AI_API_KEY=dummy-key');

        // Mock Http failure dari Gemini
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response(['error' => 'Rate limit exceeded'], 500),
        ]);

        $response = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Apa saja layanan surat yang tersedia?',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));

        // Fallback response tetap melayani informasi surat dari database
        $content = $response->json('ai_message.content');
        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Surat Keterangan Usaha (SKU)', $content);

        // Kembalikan env
        putenv('AI_PROVIDER=mock');
        config(['app.ai_provider' => 'mock']);
    }

    /**
     * Test 8: Warga menanyakan jumlah warga menerima data agregat RT & RW tempat tinggalnya
     */
    public function test_warga_asking_jumlah_warga_receives_actual_rt_and_rw_aggregate_counts(): void
    {
        $response = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Ada berapa jumlah warga saya?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        // Warga 1 berada di RT 001 RW 001. Di setup ada 1 warga di RT 001 dan 1 di RT 002 RW 002.
        $this->assertStringContainsString('RT 001', $content);
        $this->assertStringContainsString('RW 001', $content);
        $this->assertStringContainsString('1 orang warga', $content);
        $this->assertStringContainsString('privasi', $content);
    }

    /**
     * Test 9: Ketua RT menanyakan jumlah warga menerima data statistik RT-nya
     */
    public function test_rt_asking_jumlah_warga_receives_detailed_rt_statistics(): void
    {
        $response = $this->actingAs($this->userRt)->postJson(route('ai.chat.store'), [
            'message' => 'Berapa jumlah warga di wilayah saya?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        $this->assertStringContainsString('RT 001 RW 001', $content);
        $this->assertStringContainsString('Total Warga', $content);
        $this->assertStringContainsString('Laki-laki', $content);
        $this->assertStringContainsString('Total KK', $content);
    }

    /**
     * Test 10: Ketua RW menanyakan jumlah warga menerima statistik RW & rincian per RT
     */
    public function test_rw_asking_jumlah_warga_receives_rw_total_and_per_rt_breakdown(): void
    {
        $response = $this->actingAs($this->userRw)->postJson(route('ai.chat.store'), [
            'message' => 'Berapa total warga di RW saya?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        $this->assertStringContainsString('RW 001', $content);
        $this->assertStringContainsString('Total Warga', $content);
        $this->assertStringContainsString('Rincian per RT', $content);
        $this->assertStringContainsString('RT 001', $content);
    }

    /**
     * Test 11: Super Admin menanyakan jumlah warga menerima agregat seluruh desa
     */
    public function test_admin_asking_jumlah_warga_receives_village_wide_aggregates(): void
    {
        $response = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'Berapa total warga desa saat ini?',
        ]);

        $response->assertStatus(200);
        $content = $response->json('ai_message.content');

        $this->assertStringContainsString('Total Warga Seluruh Desa', $content);
        $this->assertStringContainsString('Total Kartu Keluarga (KK)', $content);
        $this->assertStringContainsString('Total Wilayah', $content);
    }

    /**
     * Test 12: RT dan RW menanyakan surat yang butuh tindakan / persetujuan
     */
    public function test_rt_and_rw_asking_pending_verifications_receive_actionable_counts(): void
    {
        // Pengajuan butuh tindakan RT
        PengajuanSurat::create([
            'warga_id' => $this->warga1->id,
            'template_id' => $this->templateSku->id,
            'status' => 'MENUNGGU_RT',
        ]);

        $responseRt = $this->actingAs($this->userRt)->postJson(route('ai.chat.store'), [
            'message' => 'Berapa pengajuan surat yang butuh persetujuan saya?',
        ]);

        $responseRt->assertStatus(200);
        $contentRt = $responseRt->json('ai_message.content');
        $this->assertStringContainsString('1 pengajuan surat', $contentRt);
        $this->assertStringContainsString('verifikasi RT', $contentRt);

        // RW saat ini belum ada yang MENUNGGU_RW
        $responseRw = $this->actingAs($this->userRw)->postJson(route('ai.chat.store'), [
            'message' => 'Berapa surat yang perlu diverifikasi?',
        ]);

        $responseRw->assertStatus(200);
        $contentRw = $responseRw->json('ai_message.content');
        $this->assertStringContainsString('0 pengajuan surat', $contentRw);
        $this->assertStringContainsString('persetujuan RW', $contentRw);
    }

    /**
     * Test 13: Otorisasi daftar nama warga - Warga ditolak demi privasi, RT/RW/Admin diizinkan sesuai wilayah
     */
    public function test_daftar_nama_warga_authorization_and_scoping(): void
    {
        // 1. Warga meminta daftar nama warga -> Ditolak dengan penjelasan privasi
        $wargaResp = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Tampilkan daftar nama warga di desa',
        ]);
        $wargaResp->assertStatus(200);
        $this->assertStringContainsString('privasi', $wargaResp->json('ai_message.content'));
        $this->assertStringNotContainsString('Siti Aminah', $wargaResp->json('ai_message.content'));

        // 2. Ketua RT 001 meminta daftar nama warga -> Hanya melihat Budi Santoso (RT 001), bukan Siti Aminah (RT 002)
        $rtResp = $this->actingAs($this->userRt)->postJson(route('ai.chat.store'), [
            'message' => 'Siapa saja daftar warga di RT saya?',
        ]);
        $rtResp->assertStatus(200);
        $rtContent = $rtResp->json('ai_message.content');
        $this->assertStringContainsString('Budi Santoso', $rtContent);
        $this->assertStringNotContainsString('Siti Aminah', $rtContent);

        // 3. Super Admin meminta daftar nama warga -> Melihat warga desa
        $adminResp = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'Daftar warga desa yang terdaftar',
        ]);
        $adminResp->assertStatus(200);
        $adminContent = $adminResp->json('ai_message.content');
        $this->assertStringContainsString('Budi Santoso', $adminContent);
        $this->assertStringContainsString('Siti Aminah', $adminContent);
    }

    /**
     * Test 14: Pertanyaan data akun pengguna berdasarkan role
     */
    public function test_pertanyaan_data_akun_berdasarkan_role(): void
    {
        // Warga hanya melihat data akun dirinya sendiri
        $wargaResp = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Bagaimana data akun pengguna saya?',
        ]);
        $wargaResp->assertStatus(200);
        $wargaContent = $wargaResp->json('ai_message.content');
        $this->assertStringContainsString('budi@warga.id', $wargaContent);
        $this->assertStringContainsString('Budi Santoso', $wargaContent);

        // Super Admin melihat rekap akun dan pembagian role
        $adminResp = $this->actingAs($this->userAdmin)->postJson(route('ai.chat.store'), [
            'message' => 'Berapa total akun pengguna terdaftar berdasarkan role?',
        ]);
        $adminResp->assertStatus(200);
        $adminContent = $adminResp->json('ai_message.content');
        $this->assertStringContainsString('Total akun pengguna', $adminContent);
        $this->assertStringContainsString('super_admin', $adminContent);
    }

    /**
     * Test 15: Pertanyaan nama ketua RT, RW, dan Kepala Desa
     */
    public function test_pertanyaan_nama_ketua_rt_rw_dan_kades(): void
    {
        // Warga 1 menanyakan siapa ketua RT dan RW
        $wargaResp = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Siapa ketua RT dan RW saya?',
        ]);
        $wargaResp->assertStatus(200);
        $wargaContent = $wargaResp->json('ai_message.content');
        $this->assertStringContainsString('Ketua RT 001', $wargaContent);
        $this->assertStringContainsString('Ketua RW 001', $wargaContent);
        $this->assertStringContainsString('H. Ahmad Sobari', $wargaContent);

        // RT menanyakan ketua wilayah
        $rtResp = $this->actingAs($this->userRt)->postJson(route('ai.chat.store'), [
            'message' => 'Siapa nama ketua RW dan Kepala Desa?',
        ]);
        $rtResp->assertStatus(200);
        $rtContent = $rtResp->json('ai_message.content');
        $this->assertStringContainsString('Ketua RW 001', $rtContent);
        $this->assertStringContainsString('H. Ahmad Sobari', $rtContent);
    }

    /**
     * Test 16: Pencegahan akses data lintas wilayah (Cross-boundary)
     */
    public function test_cross_boundary_isolation_between_rt(): void
    {
        // RT 1 tidak boleh melihat data statistik atau warga RT 2
        $service = app(AiChatContextService::class);
        $dataRt1 = $service->getDaftarWargaData($this->userRt, 'rt', 'daftar_warga');

        $this->assertEquals(1, $dataRt1['total']);
        $this->assertEquals('Budi Santoso', $dataRt1['items'][0]['nama']);
    }

    /**
     * Test 17: Penanganan sapaan natural dan tidak mengembalikan penolakan privasi
     */
    public function test_natural_greeting_without_privacy_denial(): void
    {
        $resp = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'message' => 'Halo selamat pagi',
        ]);
        $resp->assertStatus(200);
        $content = $resp->json('ai_message.content');

        $this->assertStringContainsString('Halo Budi Santoso', $content);
        $this->assertStringNotContainsString('privasi', strtolower($content));
        $this->assertStringNotContainsString('tidak dapat', strtolower($content));
    }

    /**
     * Test 18: Pertanyaan lanjutan ambigu / follow-up menghasilkan klarifikasi yang tepat
     */
    public function test_ambiguous_follow_up_asks_for_clarification(): void
    {
        $conv = AiConversation::create([
            'user_id' => $this->userWarga1->id,
            'title' => 'Percakapan Surat',
        ]);

        // Simpan pesan sebelumnya tentang Layanan Surat
        $conv->messages()->create([
            'role' => 'user',
            'content' => 'Saya mau tanya surat',
        ]);
        $conv->messages()->create([
            'role' => 'assistant',
            'content' => 'Saat ini ada beberapa Layanan Surat yang tersedia di desa.',
        ]);

        $resp = $this->actingAs($this->userWarga1)->postJson(route('ai.chat.store'), [
            'conversation_id' => $conv->id,
            'message' => 'apa saja?',
        ]);

        $resp->assertStatus(200);
        $content = $resp->json('ai_message.content');
        $this->assertStringContainsString('Maksudnya jenis surat yang ingin diajukan', $content);
        $this->assertStringContainsString('Mau tahu syarat untuk surat yang mana?', $content);
    }
}
