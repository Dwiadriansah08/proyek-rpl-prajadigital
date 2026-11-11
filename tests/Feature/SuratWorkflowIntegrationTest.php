<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $adminDesaUser;

    private User $rwUser;

    private User $rtUser;

    private User $wargaUser;

    private User $otherWargaUser;

    private SuratTemplate $template;

    private Rw $rw;

    private Rt $rt;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'super_admin']);
        $roleAdminDesa = Role::create(['name' => 'admin_desa']);
        $roleRw = Role::create(['name' => 'rw']);
        $roleRt = Role::create(['name' => 'rt']);
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

        $this->superAdmin = User::factory()->create([
            'role_id' => $roleAdmin->id,
        ]);

        $this->adminDesaUser = User::factory()->create([
            'role_id' => $roleAdminDesa->id,
        ]);

        $this->rwUser = User::factory()->create([
            'role_id' => $roleRw->id,
            'rw_id' => $this->rw->id,
        ]);

        $this->rtUser = User::factory()->create([
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $warga1 = Warga::create([
            'nik' => '3201011111110001',
            'nomor_kk' => '3201011111110002',
            'nama_lengkap' => 'Warga Pemohon',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'email' => 'warga.pemohon@example.com',
        ]);

        $this->wargaUser = User::factory()->create([
            'email' => 'warga.pemohon@example.com',
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $warga2 = Warga::create([
            'nik' => '3201012222220001',
            'nomor_kk' => '3201012222220002',
            'nama_lengkap' => 'Warga Lain',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1992-02-02',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'PNS',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'email' => 'warga.lain@example.com',
        ]);

        $this->otherWargaUser = User::factory()->create([
            'email' => 'warga.lain@example.com',
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $this->template = SuratTemplate::create([
            'nama_surat' => 'Surat Pengantar Nikah',
            'kode_surat' => 'SPN-01',
            'deskripsi' => 'Pengantar Perkawinan',
            'is_active' => true,
        ]);
    }

    public function test_full_successful_surat_lifecycle_workflow(): void
    {
        // 1. Warga mengajukan surat
        $postResponse = $this->actingAs($this->wargaUser)->post('/surat', [
            'template_id' => $this->template->id,
            'keperluan' => 'Pendaftaran KUA',
        ]);

        $postResponse->assertRedirect(route('surat.index'));
        $this->assertDatabaseHas('pengajuan_surat', [
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
        ]);

        $surat = PengajuanSurat::latest()->first();

        // 2. RT menyetujui surat -> status berubah jadi MENUNGGU_RW
        $rtApprove = $this->actingAs($this->rtUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $rtApprove->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('MENUNGGU_RW', $surat->status);

        // 3. RW menyetujui surat -> status berubah jadi MENUNGGU_ADMIN_DESA
        $rwApprove = $this->actingAs($this->rwUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $rwApprove->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('MENUNGGU_ADMIN_DESA', $surat->status);

        // 4. Admin Desa menyetujui surat -> status SELESAI, nomor surat ter-generate & timestamp tercatat
        $adminDesaApprove = $this->actingAs($this->adminDesaUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $adminDesaApprove->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();
        $this->assertEquals('SELESAI', $surat->status);
        $this->assertNotNull($surat->nomor_surat);
        $this->assertStringContainsString('RW.001', $surat->nomor_surat);
        $this->assertEquals($this->adminDesaUser->id, $surat->approved_by);
        $this->assertNotNull($surat->approved_at);
        $this->assertNotNull($surat->approved_by_admin_desa_at);
    }

    public function test_surat_rejection_workflow_sets_status_ditolak_with_catatan(): void
    {
        $surat = PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Pengajuan Surat'],
        ]);

        $rejectResponse = $this->actingAs($this->rtUser)->post("/surat/{$surat->id}/process", [
            'action' => 'reject',
            'catatan' => 'Berkas fotokopi KTP belum lengkap.',
        ]);

        $rejectResponse->assertRedirect(route('surat.show', $surat->id));
        $surat->refresh();

        $this->assertEquals('DITOLAK', $surat->status);
        $this->assertEquals('Berkas fotokopi KTP belum lengkap.', $surat->catatan);
        $this->assertEquals($this->rtUser->id, $surat->approved_by);
    }

    public function test_unauthorized_warga_cannot_view_another_wargas_surat(): void
    {
        $surat = PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Pribadi'],
        ]);

        $response = $this->actingAs($this->otherWargaUser)->get("/surat/{$surat->id}");

        $response->assertStatus(403);
    }
}
