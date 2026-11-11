<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Informasi;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use App\Models\WhatsappRecipient;
use App\Services\WhatsappNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TargetedAnnouncementAndWhatsappTest extends TestCase
{
    use RefreshDatabase;

    private Desa $desa;

    private Rw $rw1;

    private Rw $rw2;

    private Rt $rt1;

    private Rt $rt2;

    private User $superAdmin;

    private User $rw1User;

    private User $rt1User;

    private User $wargaRt1User;

    private User $wargaRt2User;

    private Warga $warga1;

    private Warga $warga2;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'super_admin']);
        $roleRw = Role::create(['name' => 'rw']);
        $roleRt = Role::create(['name' => 'rt']);
        $roleWarga = Role::create(['name' => 'warga']);

        $this->desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Provinsi Gemilang',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);

        $this->rw1 = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '001',
            'ketua_rw' => 'Ketua RW 1',
            'alamat' => 'Lingkungan RW 1',
            'status' => true,
        ]);

        $this->rw2 = Rw::create([
            'desa_id' => $this->desa->id,
            'nomor_rw' => '002',
            'ketua_rw' => 'Ketua RW 2',
            'alamat' => 'Lingkungan RW 2',
            'status' => true,
        ]);

        $this->rt1 = Rt::create([
            'rw_id' => $this->rw1->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1 RW 1',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'status' => true,
        ]);

        $this->rt2 = Rt::create([
            'rw_id' => $this->rw2->id,
            'nomor_rt' => '002',
            'ketua_rt' => 'Ketua RT 2 RW 2',
            'alamat' => 'Lingkungan RT 2 RW 2',
            'status' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@desa.test',
            'password' => bcrypt('password'),
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);

        $this->rw1User = User::create([
            'name' => 'Ketua RW 001',
            'email' => 'rw1@desa.test',
            'password' => bcrypt('password'),
            'role_id' => $roleRw->id,
            'rw_id' => $this->rw1->id,
            'is_active' => true,
        ]);

        $this->rt1User = User::create([
            'name' => 'Ketua RT 001',
            'email' => 'rt1@desa.test',
            'password' => bcrypt('password'),
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
            'is_active' => true,
        ]);

        $this->warga1 = Warga::create([
            'nik' => '3201010000000001',
            'nomor_kk' => '3201010000000001',
            'nama_lengkap' => 'Warga RT 1 RW 1',
            'tempat_lahir' => 'Sukamaju',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'nomor_telepon' => '081234567891',
            'email' => 'warga1@desa.test',
            'alamat' => 'Jl. Mawar 1',
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
            'status_aktif' => true,
        ]);

        $this->wargaRt1User = User::create([
            'name' => 'Warga RT 1 RW 1',
            'email' => 'warga1@desa.test',
            'password' => bcrypt('password'),
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
            'is_active' => true,
        ]);

        $this->warga2 = Warga::create([
            'nik' => '3201010000000002',
            'nomor_kk' => '3201010000000002',
            'nama_lengkap' => 'Warga RT 2 RW 2',
            'tempat_lahir' => 'Sukamaju',
            'tanggal_lahir' => '1992-02-02',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'nomor_telepon' => '081234567892',
            'email' => 'warga2@desa.test',
            'alamat' => 'Jl. Melati 2',
            'rw_id' => $this->rw2->id,
            'rt_id' => $this->rt2->id,
            'status_aktif' => true,
        ]);

        $this->wargaRt2User = User::create([
            'name' => 'Warga RT 2 RW 2',
            'email' => 'warga2@desa.test',
            'password' => bcrypt('password'),
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw2->id,
            'rt_id' => $this->rt2->id,
            'is_active' => true,
        ]);
    }

    /**
     * 1. Test pembuatan pengumuman bertarget Desa oleh Super Admin
     */
    public function test_super_admin_can_create_targeted_announcement_for_whole_desa(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('informasi.store'), [
            'judul' => 'Pengumuman Seluruh Desa',
            'kategori' => 'Pengumuman',
            'isi' => 'Konten penting untuk seluruh warga.',
            'target_type' => 'desa',
            'status_publikasi' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('informasi', [
            'judul' => 'Pengumuman Seluruh Desa',
            'target_type' => 'desa',
            'scope_wilayah' => 'Desa',
        ]);
    }

    /**
     * 2. Test pembatasan kewenangan: RT hanya boleh membuat untuk RT-nya sendiri
     */
    public function test_rt_cannot_create_announcement_outside_their_territory(): void
    {
        $this->actingAs($this->rt1User)->post(route('informasi.store'), [
            'judul' => 'Pengumuman RT 1',
            'kategori' => 'Pengumuman',
            'isi' => 'Konten khusus RT 1.',
            'target_type' => 'desa', // mencoba spoof ke desa
            'status_publikasi' => 1,
        ]);

        // Backend secara tegas memaksa scope ke RT milik user RT tersebut
        $this->assertDatabaseHas('informasi', [
            'judul' => 'Pengumuman RT 1',
            'target_type' => 'rt',
            'rt_id' => $this->rt1->id,
            'rw_id' => $this->rw1->id,
        ]);
    }

    /**
     * 3. Test visibilitas: Pengumuman khusus RT 1 tidak boleh dilihat oleh Warga RT 2
     */
    public function test_targeted_announcement_is_hidden_from_other_wilayah(): void
    {
        $infoRt1 = Informasi::create([
            'judul' => 'Rahasia Warga RT 1',
            'kategori' => 'Pengumuman',
            'isi' => 'Khusus warga RT 1 saja.',
            'author_id' => $this->rt1User->id,
            'target_type' => 'rt',
            'scope_wilayah' => 'RT',
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
            'status_publikasi' => true,
        ]);

        // Warga RT 1 bisa akses (200)
        $resWarga1 = $this->actingAs($this->wargaRt1User)->get(route('informasi.show', $infoRt1->id));
        $resWarga1->assertStatus(200);

        // Warga RT 2 terhalang 403 Forbidden
        $resWarga2 = $this->actingAs($this->wargaRt2User)->get(route('informasi.show', $infoRt1->id));
        $resWarga2->assertStatus(403);
    }

    /**
     * 4. Test resolusi penerima dan validasi nomor telepon
     */
    public function test_recipient_resolution_and_phone_validation(): void
    {
        $waService = app(WhatsappNotificationService::class);

        // Test normalisasi & validasi
        $this->assertEquals('6281234567890', $waService->normalizePhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', $waService->normalizePhoneNumber('6281234567890'));
        $this->assertTrue($waService->isValidPhoneNumber('081234567890'));
        $this->assertFalse($waService->isValidPhoneNumber('12345')); // nomor invalid

        // Buat pengumuman RW 1
        $infoRw1 = Informasi::create([
            'judul' => 'Info RW 1',
            'kategori' => 'Pengumuman',
            'isi' => 'Konten RW 1',
            'author_id' => $this->rw1User->id,
            'target_type' => 'rw',
            'scope_wilayah' => 'RW',
            'rw_id' => $this->rw1->id,
            'status_publikasi' => true,
        ]);

        $recipients = $waService->resolveRecipients($infoRw1);

        // Hanya warga1 yang masuk (karena berada di RW 1)
        $this->assertCount(1, $recipients);
        $this->assertEquals('6281234567891', $recipients->first()['nomor_telepon']);
    }

    /**
     * 5. Test konfirmasi eksplisit dan pencegahan pengiriman duplikat
     */
    public function test_broadcast_requires_explicit_confirmation_and_prevents_duplicate_sends(): void
    {
        $info = Informasi::create([
            'judul' => 'Info Gotong Royong',
            'kategori' => 'Pengumuman',
            'isi' => 'Mari kerja bakti.',
            'author_id' => $this->superAdmin->id,
            'target_type' => 'desa',
            'scope_wilayah' => 'Desa',
            'status_publikasi' => true,
        ]);

        // Coba kirim tanpa konfirmasi checkbox -> error validation
        $resNoConfirm = $this->actingAs($this->superAdmin)->post(route('informasi.broadcast', $info->id), []);
        $resNoConfirm->assertSessionHasErrors('confirm_broadcast');

        // Kirim dengan konfirmasi
        $resConfirm = $this->actingAs($this->superAdmin)->post(route('informasi.broadcast', $info->id), [
            'confirm_broadcast' => 1,
        ]);

        $resConfirm->assertRedirect(route('informasi.show', $info->id));
        $resConfirm->assertSessionHas('success');

        // Pastikan record penerima tersimpan dengan status 'berhasil' (simulasi sukses)
        $this->assertDatabaseHas('whatsapp_recipients', [
            'informasi_id' => $info->id,
            'recipient_phone' => '6281234567891',
            'status' => 'berhasil',
        ]);

        // Uji Pencegahan Duplikasi: Kirim kedua kalinya untuk pengumuman yang sama
        $resDuplicate = $this->actingAs($this->superAdmin)->post(route('informasi.broadcast', $info->id), [
            'confirm_broadcast' => 1,
        ]);

        // Sistem memberitahu bahwa tidak ada nomor baru yang perlu dikirim (semua sudah pernah menerima)
        $resDuplicate->assertSessionHas('warning');
        $this->assertEquals(2, WhatsappRecipient::where('informasi_id', $info->id)->count());
    }

    /**
     * 6. Test penanganan simulasi vs kegagalan gateway
     */
    public function test_gateway_failure_handling_records_failed_status(): void
    {
        // Simulasikan mode live dengan token palsu
        config([
            'services.whatsapp.simulation' => false,
            'services.whatsapp.token' => 'dummy-fonnte-token',
        ]);

        // Mock Http gateway mengembalikan error/kuota habis
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'Device disconnected or quota exceeded',
            ], 400),
        ]);

        $waService = app(WhatsappNotificationService::class);
        $result = $waService->sendNotification('081234567899', 'Pesan Test Live');

        $this->assertFalse($result['success']);
        $this->assertEquals('Device disconnected or quota exceeded', $result['message']);
    }

    /**
     * 7. Test user phone storage, validation, and relationship synchronization
     */
    public function test_user_phone_can_be_stored_and_synchronized_with_warga(): void
    {
        $roleRt = Role::where('name', 'rt')->first();

        // Tambah user baru dengan nomor telepon
        $resStore = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'Budi RT User',
            'email' => 'budi_rt@desa.test',
            'nomor_telepon' => '085678901234',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $roleRt->id,
            'is_active' => 1,
        ]);

        $resStore->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'budi_rt@desa.test',
            'nomor_telepon' => '085678901234',
        ]);

        $budiUser = User::where('email', 'budi_rt@desa.test')->first();

        // Hubungkan budiUser dengan record Warga untuk menguji sinkronisasi
        $wargaBudi = Warga::create([
            'nik' => '3201010000000099',
            'nomor_kk' => '3201010000000099',
            'nama_lengkap' => 'Budi RT User',
            'email' => 'budi_rt@desa.test',
            'nomor_telepon' => '085678901234',
            'jenis_kelamin' => 'Laki-laki',
            'rw_id' => $this->rw1->id,
            'rt_id' => $this->rt1->id,
            'status_aktif' => true,
        ]);

        // Edit user mengubah nomor telepon
        $resUpdate = $this->actingAs($this->superAdmin)->put(route('users.update', $budiUser->id), [
            'name' => 'Budi RT User Updated',
            'email' => 'budi_rt@desa.test',
            'nomor_telepon' => '085699998888',
            'role_id' => $roleRt->id,
            'is_active' => 1,
        ]);

        $resUpdate->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $budiUser->id,
            'nomor_telepon' => '085699998888',
        ]);

        // Sinkronisasi otomatis ke Warga yang memiliki email sama
        $this->assertDatabaseHas('warga', [
            'id' => $wargaBudi->id,
            'nomor_telepon' => '085699998888',
        ]);
    }

    /**
     * 8. Test live Fonnte successful response handling via HTTP fake
     */
    public function test_live_fonnte_success_response_handling_with_http_fake(): void
    {
        config([
            'services.whatsapp.simulation' => false,
            'services.whatsapp.token' => 'live-fonnte-token-test',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'target' => ['6281234567891'],
                'reason' => 'success',
            ], 200),
        ]);

        $waService = app(WhatsappNotificationService::class);
        $result = $waService->sendNotification('+6281234567891', 'Pesan Test Live Berhasil');

        $this->assertTrue($result['success']);
        $this->assertEquals('sent', $result['status']);
        $this->assertEquals('success', $result['message']);
    }

    /**
     * 9. Test normalisasi nomor Indonesia yang konsisten
     */
    public function test_indonesian_phone_number_normalization_variations(): void
    {
        $waService = app(WhatsappNotificationService::class);

        // Format 08...
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('08123456789'));
        // Format +628...
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('+628123456789'));
        // Format 628...
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('628123456789'));
        // Format dengan strip dan spasi 0812-3456-789
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('0812-3456-789'));
        // Format 8...
        $this->assertEquals('628123456789', $waService->normalizePhoneNumber('8123456789'));
    }
}
