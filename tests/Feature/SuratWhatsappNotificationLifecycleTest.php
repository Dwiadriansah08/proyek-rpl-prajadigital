<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratNotification;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Models\Warga;
use App\Services\WhatsappNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SuratWhatsappNotificationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $rtUser;

    private User $rwUser;

    private User $adminDesaUser;

    private User $wargaUser;

    private Warga $warga;

    private SuratTemplate $template;

    private Rw $rw;

    private Rt $rt;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.token' => 'test-fonnte-token-secret',
            'services.whatsapp.simulation' => false,
            'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        ]);

        $roleRw = Role::create(['name' => 'rw']);
        $roleRt = Role::create(['name' => 'rt']);
        $roleAdminDesa = Role::create(['name' => 'admin_desa']);
        $roleWarga = Role::create(['name' => 'warga']);

        $desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Provinsi Gemilang',
            'alamat' => 'Jl. Merdeka No. 1',
        ]);

        $this->rw = Rw::create([
            'desa_id' => $desa->id,
            'nomor_rw' => '001',
            'ketua_rw' => 'Ketua RW 1',
            'status' => true,
        ]);

        $this->rt = Rt::create([
            'rw_id' => $this->rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1',
            'status' => true,
        ]);

        $this->rtUser = User::factory()->create([
            'name' => 'Pak RT 01',
            'email' => 'rt@desa.id',
            'nomor_telepon' => '081299990001',
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $this->rwUser = User::factory()->create([
            'name' => 'Pak RW 01',
            'email' => 'rw@desa.id',
            'nomor_telepon' => '081299990002',
            'role_id' => $roleRw->id,
            'rw_id' => $this->rw->id,
        ]);

        $this->adminDesaUser = User::factory()->create([
            'name' => 'Admin Desa',
            'email' => 'admindesa@desa.id',
            'nomor_telepon' => '081299990003',
            'role_id' => $roleAdminDesa->id,
        ]);

        $this->warga = Warga::create([
            'nik' => '3201010101010055',
            'nomor_kk' => '3201010101010099',
            'nama_lengkap' => 'Ahmad Warga',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1995-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Wiraswasta',
            'email' => 'ahmad@warga.id',
            'nomor_telepon' => '081288880055',
            'alamat' => 'Jl. Mawar 10',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
        ]);

        $this->wargaUser = User::factory()->create([
            'name' => 'Ahmad Warga',
            'email' => 'ahmad@warga.id',
            'nomor_telepon' => '081288880055',
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $this->template = SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Usaha',
            'kode_surat' => 'SKU-01',
            'deskripsi' => 'Surat Keterangan Usaha',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Saat warga mengajukan surat baru (MENUNGGU_RT), notifikasi WA terkirim ke RT terkait
     */
    public function test_warga_submitting_surat_triggers_notification_to_rt(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $response = $this->actingAs($this->wargaUser)->post('/surat', [
            'template_id' => $this->template->id,
            'keperluan' => 'Peminjaman Modal Usaha',
        ]);

        $response->assertRedirect(route('surat.index'));

        $surat = PengajuanSurat::latest()->first();
        $this->assertNotNull($surat);

        // Pastikan record notifikasi tersimpan di tabel surat_notifications
        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'MENUNGGU_RT',
            'recipient_role' => 'rt',
            'recipient_phone' => '6281299990001',
            'status' => 'sent',
        ]);

        // Verifikasi Http Request dikirim ke nomor telepon RT dengan header otorisasi Fonnte
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request['target'] === '6281299990001'
                && str_contains($request['message'], 'Surat Keterangan Usaha')
                && str_contains($request['message'], 'Ahmad Warga')
                && $request->header('Authorization')[0] === 'test-fonnte-token-secret';
        });
    }

    /**
     * Test 2: Saat RT menyetujui (MENUNGGU_RW), notifikasi terkirim ke RW dan ke Warga
     */
    public function test_rt_approving_surat_triggers_notification_to_rw_and_warga(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Usaha Warung'],
        ]);

        $response = $this->actingAs($this->rtUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $response->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('MENUNGGU_RW', $surat->status);

        // Notifikasi ke RW
        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'MENUNGGU_RW',
            'recipient_role' => 'rw',
            'recipient_phone' => '6281299990002',
            'status' => 'sent',
        ]);

        // Notifikasi ke Warga
        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'MENUNGGU_RW',
            'recipient_role' => 'warga',
            'recipient_phone' => '6281288880055',
            'status' => 'sent',
        ]);

        Http::assertSent(function ($request) {
            return $request['target'] === '6281299990002'
                && str_contains($request['message'], 'VERIFIKASI SURAT (RW)');
        });

        Http::assertSent(function ($request) {
            return $request['target'] === '6281288880055'
                && str_contains($request['message'], 'DISETUJUI OLEH RT');
        });
    }

    /**
     * Test 3: Saat RW menyetujui, surat berubah ke MENUNGGU_ADMIN_DESA dan notifikasi terkirim.
     * Lalu saat Admin Desa menyetujui (SELESAI), notifikasi nomor surat terkirim ke Warga.
     */
    public function test_rw_and_admin_desa_approval_lifecycle_notifications(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RW',
            'data_tambahan' => ['keperluan' => 'Usaha Warung'],
        ]);

        // 1. RW menyetujui -> status MENUNGGU_ADMIN_DESA
        $responseRw = $this->actingAs($this->rwUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $responseRw->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('MENUNGGU_ADMIN_DESA', $surat->status);

        // Verifikasi notifikasi terkirim ke Admin Desa dan Warga
        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'MENUNGGU_ADMIN_DESA',
            'recipient_role' => 'admin_desa',
            'recipient_phone' => '6281299990003',
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'MENUNGGU_ADMIN_DESA',
            'recipient_role' => 'warga',
            'recipient_phone' => '6281288880055',
            'status' => 'sent',
        ]);

        // 2. Admin Desa menyetujui -> status SELESAI
        $responseAdmin = $this->actingAs($this->adminDesaUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $responseAdmin->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('SELESAI', $surat->status);

        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'SELESAI',
            'recipient_role' => 'warga',
            'recipient_phone' => '6281288880055',
            'status' => 'sent',
        ]);

        Http::assertSent(function ($request) use ($surat) {
            return $request['target'] === '6281288880055'
                && str_contains($request['message'], 'SELESAI DIPROSES DAN DISETUJUI')
                && str_contains($request['message'], $surat->nomor_surat);
        });
    }

    /**
     * Test 4: Saat surat ditolak di tahap mana pun (DITOLAK), alasan penolakan terkirim ke Warga
     */
    public function test_rejection_at_any_stage_notifies_warga_with_reason(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Usaha Warung'],
        ]);

        $response = $this->actingAs($this->rtUser)->post("/surat/{$surat->id}/process", [
            'action' => 'reject',
            'catatan' => 'Foto dokumen KK buram, mohon upload ulang.',
        ]);

        $response->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('DITOLAK', $surat->status);

        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'DITOLAK',
            'recipient_role' => 'warga',
            'recipient_phone' => '6281288880055',
            'status' => 'sent',
        ]);

        Http::assertSent(function ($request) {
            return $request['target'] === '6281288880055'
                && str_contains($request['message'], 'DITOLAK')
                && str_contains($request['message'], 'Foto dokumen KK buram, mohon upload ulang.');
        });
    }

    /**
     * Test 5: Pencegahan duplikasi / Notifikasi Ganda (Idempotensi)
     */
    public function test_idempotency_prevents_duplicate_whatsapp_notifications(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
        ]);

        $service = app(WhatsappNotificationService::class);

        // Panggil notifyStatusUpdate pertama
        $service->notifyStatusUpdate($surat, 'MENUNGGU_RT');

        // Panggil notifyStatusUpdate kedua (duplikat event yang sama)
        $service->notifyStatusUpdate($surat, 'MENUNGGU_RT');

        // Harus hanya ada 1 request yang terkirim ke Fonnte
        Http::assertSentCount(1);
        $this->assertEquals(1, SuratNotification::where('pengajuan_surat_id', $surat->id)->count());
    }

    /**
     * Test 6: Kegagalan gateway Fonnte dicatat dan TIDAK membatalkan pengajuan surat
     */
    public function test_gateway_failure_does_not_abort_transaction_and_records_failure(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'Device disconnected',
            ], 500),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
        ]);

        $response = $this->actingAs($this->rtUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        // Pengajuan surat tetap berhasil diperbarui ke MENUNGGU_RW
        $response->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('MENUNGGU_RW', $surat->status);

        // Status notifikasi dicatat 'failed' dan tidak mengklaim terkirim
        $this->assertDatabaseHas('surat_notifications', [
            'pengajuan_surat_id' => $surat->id,
            'event_status' => 'MENUNGGU_RW',
            'status' => 'failed',
        ]);

        $notif = SuratNotification::where('pengajuan_surat_id', $surat->id)->first();
        $this->assertNotNull($notif->error_message);
        $this->assertNull($notif->sent_at);
    }

    /**
     * Test 7: Nomor telepon tidak valid tidak dikirim ke gateway Fonnte
     */
    public function test_invalid_phone_number_is_ignored_and_not_sent(): void
    {
        Http::fake();

        // Ganti nomor RT menjadi tidak valid
        $this->rtUser->update(['nomor_telepon' => '12345']);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
        ]);

        $service = app(WhatsappNotificationService::class);
        $service->notifyStatusUpdate($surat, 'MENUNGGU_RT');

        // Tidak ada HTTP request yang dikirim
        Http::assertNothingSent();
    }

    /**
     * Test 8: Pengesahan Admin Desa mengirimkan notifikasi WhatsApp teks bahwa surat selesai dan PDF dikirim ke email
     */
    public function test_admin_desa_approval_sends_whatsapp_with_pdf_document_attachment(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_ADMIN_DESA',
            'data_tambahan' => ['keperluan' => 'Pendaftaran Usaha UMKM'],
        ]);

        $response = $this->actingAs($this->adminDesaUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $response->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('SELESAI', $surat->status);
        $this->assertNotNull($surat->file_pdf);

        // Verifikasi Http Request Fonnte menerima pesan teks pemberitahuan surat selesai dan pengecekan email
        Http::assertSent(function ($request) {
            $hasTarget = $request['target'] === '6281288880055';
            $hasText = str_contains($request['message'], 'SELESAI DIPROSES DAN DISETUJUI')
                && str_contains($request['message'], 'email Anda');
            $noUrl = empty($request['url']);
            $noInvalidFileKey = ! isset($request['file']);

            return $hasTarget && $hasText && $noUrl && $noInvalidFileKey;
        });
    }

    /**
     * Test 9: Pemanggilan sendSuratSelesaiNotification secara langsung bekerja dengan baik
     */
    public function test_send_surat_selesai_notification_method_directly(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $dummyPdfName = 'test_surat_999.pdf';
        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'SELESAI',
            'nomor_surat' => '470/999/RW.001/2026',
            'file_pdf' => $dummyPdfName,
        ]);

        $service = app(WhatsappNotificationService::class);
        $result = $service->sendSuratSelesaiNotification($surat, asset('storage/'.$dummyPdfName));

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) use ($dummyPdfName) {
            return $request['target'] === '6281288880055'
                && ! empty($request['url'])
                && str_contains($request['url'], $dummyPdfName)
                && str_contains($request['message'], 'lampiran dokumen di bawah ini');
        });
    }

    /**
     * Test 10: Notifikasi alur biasa (MENUNGGU_RT / DITOLAK) TIDAK menyertakan parameter file/url
     */
    public function test_regular_notifications_do_not_contain_file_attachment_parameter(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'message' => 'Pesan terkirim',
            ], 200),
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
        ]);

        $service = app(WhatsappNotificationService::class);
        $service->notifyStatusUpdate($surat, 'MENUNGGU_RT');

        Http::assertSent(function ($request) {
            return ! isset($request['url']) && ! isset($request['file']);
        });
    }

    /**
     * Test 11: isLocalUrl() mendeteksi host lokal dengan benar
     */
    public function test_is_local_url_correctly_identifies_local_and_public_hosts(): void
    {
        $service = app(WhatsappNotificationService::class);

        // Host yang harus dianggap LOKAL (tidak bisa diakses Fonnte dari internet)
        $this->assertTrue($service->isLocalUrl('http://localhost/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://127.0.0.1/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://rpl.test/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://myapp.local/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://192.168.1.10/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://10.0.0.5/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://172.20.1.1/storage/surat.pdf'));
        $this->assertTrue($service->isLocalUrl('http://app.dev/storage/surat.pdf'));

        // Host yang harus dianggap PUBLIK (bisa diakses Fonnte dari internet)
        $this->assertFalse($service->isLocalUrl('https://myapp.com/storage/surat.pdf'));
        $this->assertFalse($service->isLocalUrl('https://xxxx.ngrok-free.app/storage/surat.pdf'));
        $this->assertFalse($service->isLocalUrl('https://api.prajadigital.id/storage/surat.pdf'));
        $this->assertFalse($service->isLocalUrl('https://1.2.3.4/storage/surat.pdf'));
    }

    /**
     * Test 12: WHATSAPP_PUBLIC_URL me-rewrite URL lokal menjadi URL publik sebelum dikirim ke Fonnte
     */
    public function test_whatsapp_public_url_override_rewrites_local_pdf_url_to_public(): void
    {
        config([
            'app.url' => 'http://rpl.test',
            'services.whatsapp.public_url' => 'https://abc123.ngrok-free.app',
        ]);

        $service = app(WhatsappNotificationService::class);

        // URL lokal yang seharusnya di-rewrite oleh WHATSAPP_PUBLIC_URL
        $localUrl = 'http://rpl.test/storage/surat_42_123456.pdf';
        $resolved = $service->resolvePublicPdfUrl($localUrl);

        $this->assertEquals('https://abc123.ngrok-free.app/storage/surat_42_123456.pdf', $resolved);
        // URL hasil rewrite harus publik (tidak lokal lagi)
        $this->assertFalse($service->isLocalUrl($resolved));
    }

    /**
     * Test 13: resolvePublicPdfUrl mengembalikan URL lokal apa adanya jika tidak ada override
     *          (agar developer tahu URL mana yang SEHARUSNYA diganti dengan tunnel)
     */
    public function test_resolve_public_pdf_url_without_override_returns_app_url_based_url(): void
    {
        config([
            'app.url' => 'http://rpl.test',
            'services.whatsapp.public_url' => null,
        ]);

        $service = app(WhatsappNotificationService::class);

        // Nama file PDF di storage — harus diresolvemenjadi URL storage publik berbasis APP_URL
        $resolved = $service->resolvePublicPdfUrl('surat_99_999.pdf');

        $this->assertStringContainsString('surat_99_999.pdf', $resolved);
        $this->assertStringContainsString('/storage/', $resolved);
        // Tanpa override, URL masih lokal — isLocalUrl harus true
        $this->assertTrue($service->isLocalUrl($resolved));
    }
}
