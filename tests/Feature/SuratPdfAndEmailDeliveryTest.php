<?php

namespace Tests\Feature;

use App\Mail\SuratSelesaiMail;
use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Models\Warga;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SuratPdfAndEmailDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $rtUser;

    private User $rwUser;

    private User $adminDesaUser;

    private User $wargaUser;

    private Warga $warga;

    private Rw $rw;

    private Rt $rt;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.token' => 'dummy-fonnte-token',
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
            'kode_pos' => '40111',
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

        $this->rwUser = User::factory()->create([
            'name' => 'Pak RW 01',
            'email' => 'rw@desa.id',
            'nomor_telepon' => '081299990002',
            'role_id' => $roleRw->id,
            'rw_id' => $this->rw->id,
        ]);

        $this->rtUser = User::factory()->create([
            'name' => 'Pak RT 01',
            'email' => 'rt@desa.id',
            'nomor_telepon' => '081299990003',
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $this->adminDesaUser = User::factory()->create([
            'name' => 'Admin Desa',
            'email' => 'admindesa@desa.id',
            'nomor_telepon' => '081299990004',
            'role_id' => $roleAdminDesa->id,
        ]);

        $this->warga = Warga::create([
            'nik' => '3201010101010088',
            'nomor_kk' => '3201010101010099',
            'nama_lengkap' => 'Budi Pratama',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1995-05-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Wiraswasta',
            'nomor_telepon' => '081288880088',
            'email' => 'budi.pratama@desa.id',
            'alamat' => 'Jl. Mawar No. 10',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'status_aktif' => true,
        ]);

        $this->wargaUser = User::factory()->create([
            'name' => 'Budi Pratama',
            'email' => 'budi.pratama@desa.id',
            'nomor_telepon' => '081288880088',
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);
    }

    /**
     * Test render all 7 PDF templates via DomPDF without error.
     */
    public function test_all_seven_pdf_templates_render_successfully(): void
    {
        $templatesConfig = [
            ['kode' => 'SK-01', 'nama' => 'Surat Pengantar RT/RW', 'view' => 'surat.pdf.pengantar_rtrw'],
            ['kode' => 'SK-02', 'nama' => 'Surat Keterangan Domisili', 'view' => 'surat.pdf.domisili'],
            ['kode' => 'SK-03', 'nama' => 'Surat Keterangan Tidak Mampu', 'view' => 'surat.pdf.sktm'],
            ['kode' => 'SK-04', 'nama' => 'Surat Keterangan Kematian', 'view' => 'surat.pdf.kematian'],
            ['kode' => 'SK-05', 'nama' => 'Surat Keterangan Usaha', 'view' => 'surat.pdf.sku'],
            ['kode' => 'SK-06', 'nama' => 'Surat Keterangan Berkelakuan Baik', 'view' => 'surat.pdf.berkelakuan_baik'],
            ['kode' => 'SK-07', 'nama' => 'Surat Keterangan Penghasilan', 'view' => 'surat.pdf.penghasilan'],
        ];

        foreach ($templatesConfig as $cfg) {
            $template = SuratTemplate::create([
                'kode_surat' => $cfg['kode'],
                'nama_surat' => $cfg['nama'],
                'is_active' => true,
            ]);

            $surat = PengajuanSurat::create([
                'warga_id' => $this->warga->id,
                'template_id' => $template->id,
                'status' => 'SELESAI',
                'nomor_surat' => "470/001/{$cfg['kode']}/2026",
                'data_tambahan' => [
                    'keperluan' => "Pengujian Template {$cfg['nama']}",
                    'nama_usaha' => 'Toko Barokah Jaya',
                    'nominal_penghasilan' => 5000000,
                    'nama_almarhum' => 'Almarhum Bapak Fulan',
                    'tanggal_meninggal' => '2026-10-10',
                ],
                'approved_at' => now(),
                'approved_by' => $this->rwUser->id,
            ]);

            $pdf = Pdf::loadView($cfg['view'], ['surat' => $surat]);
            $output = $pdf->output();

            $this->assertNotEmpty($output, "PDF untuk view {$cfg['view']} tidak boleh kosong.");
            $this->assertStringStartsWith('%PDF', $output, "Output untuk view {$cfg['view']} harus berupa berkas PDF valid.");
        }
    }

    /**
     * Test that Admin Desa final approval generates PDF, dispatches SuratSelesaiMail with
     * PDF attachment to warga email, and sends text-only WA notification.
     *
     * Flow: MENUNGGU_ADMIN_DESA -> Admin Desa approves -> SELESAI + email + WA
     */
    public function test_admin_desa_approval_dispatches_email_with_pdf_attachment_and_text_only_wa(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'message' => 'Pesan terkirim'], 200),
        ]);

        $template = SuratTemplate::create([
            'kode_surat' => 'SK-05',
            'nama_surat' => 'Surat Keterangan Usaha',
            'is_active' => true,
        ]);

        // Surat already forwarded by RW, now awaiting Admin Desa final verification
        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $template->id,
            'status' => 'MENUNGGU_ADMIN_DESA',
            'data_tambahan' => [
                'keperluan' => 'Pengajuan Modal Usaha',
                'nama_usaha' => 'Warung Kopi Digital',
            ],
        ]);

        // Admin Desa memberikan persetujuan akhir -> SELESAI
        $response = $this->actingAs($this->adminDesaUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $response->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('SELESAI', $surat->status);
        $this->assertNotNull($surat->file_pdf);
        $this->assertNotNull($surat->approved_by_admin_desa_at);

        // 1. Verifikasi Email Mailable terkirim ke email pemohon
        Mail::assertSent(SuratSelesaiMail::class, function ($mail) use ($surat) {
            $hasRecipient = $mail->hasTo('budi.pratama@desa.id');
            $hasSameSurat = $mail->surat->id === $surat->id;
            $hasPdfAttachment = file_exists($mail->pdfPath);

            return $hasRecipient && $hasSameSurat && $hasPdfAttachment;
        });

        // 2. Verifikasi WhatsApp Fonnte terkirim hanya pesan teks tanpa field url / file
        Http::assertSent(function ($request) {
            $isTarget = $request['target'] === '6281288880088';
            $hasEmailNotice = str_contains($request['message'], 'email Anda (budi.pratama@desa.id)');
            $hasNoUrl = empty($request['url']);
            $hasNoFile = ! isset($request['file']);

            return $isTarget && $hasEmailNotice && $hasNoUrl && $hasNoFile;
        });
    }

    /**
     * Test that RW approval changes status to MENUNGGU_ADMIN_DESA (not SELESAI),
     * and sends a WhatsApp notification to Admin Desa/Super Admin users.
     */
    public function test_rw_approval_moves_status_to_menunggu_admin_desa(): void
    {
        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true, 'message' => 'Pesan terkirim'], 200),
        ]);

        $template = SuratTemplate::create([
            'kode_surat' => 'SK-02',
            'nama_surat' => 'Surat Keterangan Domisili',
            'is_active' => true,
        ]);

        $surat = PengajuanSurat::create([
            'warga_id' => $this->warga->id,
            'template_id' => $template->id,
            'status' => 'MENUNGGU_RW',
            'data_tambahan' => [
                'keperluan' => 'Pembuatan KTP',
            ],
        ]);

        $response = $this->actingAs($this->rwUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $response->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();

        // RW approval must set status to MENUNGGU_ADMIN_DESA, not SELESAI
        $this->assertEquals('MENUNGGU_ADMIN_DESA', $surat->status);
        $this->assertNull($surat->file_pdf, 'PDF harus belum digenerate pada tahap RW.');
        $this->assertNull($surat->approved_by_admin_desa_at, 'approved_by_admin_desa_at harus null sebelum Admin Desa menyetujui.');
    }
}
