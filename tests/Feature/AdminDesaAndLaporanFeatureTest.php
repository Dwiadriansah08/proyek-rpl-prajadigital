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

class AdminDesaAndLaporanFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $adminDesa;

    private User $superAdmin;

    private User $rwUser;

    private User $rtUser;

    private User $wargaUser;

    private Desa $desa;

    private Rw $rw;

    private Rt $rt;

    private SuratTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuperAdmin = Role::create(['name' => 'super_admin']);
        $roleAdminDesa = Role::create(['name' => 'admin_desa']);
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

        $this->rw = Rw::create([
            'desa_id' => $this->desa->id,
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

        $this->superAdmin = User::factory()->create([
            'role_id' => $roleSuperAdmin->id,
            'email' => 'superadmin@desa.id',
        ]);

        $this->adminDesa = User::factory()->create([
            'role_id' => $roleAdminDesa->id,
            'email' => 'admindesa@desa.id',
        ]);

        $this->rwUser = User::factory()->create([
            'role_id' => $roleRw->id,
            'rw_id' => $this->rw->id,
            'email' => 'rw@desa.id',
        ]);

        $this->rtUser = User::factory()->create([
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'email' => 'rt@desa.id',
        ]);

        $warga = Warga::create([
            'nik' => '3201019999990001',
            'nomor_kk' => '3201019999990002',
            'nama_lengkap' => 'Warga Laporan',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'Jl. Mawar',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'email' => 'warga.laporan@desa.id',
        ]);

        $this->wargaUser = User::factory()->create([
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'email' => 'warga.laporan@desa.id',
        ]);

        $this->template = SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Domisili',
            'kode_surat' => 'SKD-01',
            'deskripsi' => 'Surat Domisili Warga',
            'is_active' => true,
        ]);
    }

    /**
     * Admin Desa can access dashboard and view custom statistics and chart data.
     */
    public function test_admin_desa_can_view_dashboard_with_stats(): void
    {
        $response = $this->actingAs($this->adminDesa)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('stats');
        $response->assertSee('Verifikasi Desa');
        $response->assertSee('Tren Pengajuan Surat Bulanan');
    }

    /**
     * Admin Desa can access laporan page and filter results.
     */
    public function test_admin_desa_can_access_laporan_index_with_filters(): void
    {
        PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'SELESAI',
            'nomor_surat' => '470/001/SKD-01/2026',
            'data_tambahan' => ['keperluan' => 'Pekerjaan'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminDesa)->get(route('laporan.index', [
            'status' => 'SELESAI',
            'bulan' => now()->format('m'),
            'tahun' => now()->format('Y'),
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('laporan.index');
        $response->assertViewHas(['summary', 'pengajuan']);
        $response->assertSee('470/001/SKD-01/2026');
    }

    /**
     * Admin Desa can export laporan to PDF.
     */
    public function test_admin_desa_can_export_laporan_to_pdf(): void
    {
        PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'SELESAI',
            'nomor_surat' => '470/002/SKD-01/2026',
            'data_tambahan' => ['keperluan' => 'Pekerjaan'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminDesa)->get(route('laporan.export-pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Admin Desa can export laporan to CSV / Excel.
     */
    public function test_admin_desa_can_export_laporan_to_excel_csv(): void
    {
        PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'SELESAI',
            'nomor_surat' => '470/003/SKD-01/2026',
            'data_tambahan' => ['keperluan' => 'Pekerjaan'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminDesa)->get(route('laporan.export-excel'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    /**
     * Admin Desa is strictly restricted from Konfigurasi Sistem (Super Admin only).
     */
    public function test_admin_desa_cannot_access_konfigurasi_sistem(): void
    {
        $response = $this->actingAs($this->adminDesa)->get('/konfigurasi');

        $response->assertStatus(403);
    }

    /**
     * Unauthorized roles (RW, RT, Warga) cannot access laporan routes.
     */
    public function test_unauthorized_roles_cannot_access_laporan(): void
    {
        $this->actingAs($this->rwUser)->get(route('laporan.index'))->assertStatus(403);
        $this->actingAs($this->rtUser)->get(route('laporan.index'))->assertStatus(403);
        $this->actingAs($this->wargaUser)->get(route('laporan.index'))->assertStatus(403);
    }
}
