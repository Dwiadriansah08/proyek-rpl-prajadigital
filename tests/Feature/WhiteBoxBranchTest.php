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

class WhiteBoxBranchTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $rwUser;

    private User $rtUser;

    private User $wargaUser;

    private SuratTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'super_admin']);
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

        $rw = Rw::create([
            'desa_id' => $desa->id,
            'nomor_rw' => '001',
            'ketua_rw' => 'Ketua RW 1',
            'alamat' => 'Lingkungan RW 1',
            'status' => true,
        ]);

        $rt = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'status' => true,
        ]);

        $this->superAdmin = User::factory()->create(['role_id' => $roleAdmin->id]);
        $this->rwUser = User::factory()->create(['role_id' => $roleRw->id, 'rw_id' => $rw->id]);
        $this->rtUser = User::factory()->create(['role_id' => $roleRt->id, 'rw_id' => $rw->id, 'rt_id' => $rt->id]);

        $wargaModel = Warga::create([
            'nik' => '3201018888880001',
            'nomor_kk' => '3201018888880002',
            'nama_lengkap' => 'Warga Whitebox',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'RT 1 RW 1',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'rw_id' => $rw->id,
            'rt_id' => $rt->id,
            'email' => 'warga.whitebox@example.com',
        ]);

        $this->wargaUser = User::factory()->create([
            'email' => 'warga.whitebox@example.com',
            'role_id' => $roleWarga->id,
            'rw_id' => $rw->id,
            'rt_id' => $rt->id,
        ]);

        $this->template = SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Domisili',
            'kode_surat' => 'SKD-01',
            'deskripsi' => 'Keterangan Domisili',
            'is_active' => true,
        ]);
    }

    /**
     * White-Box: Branch 1 (RW cannot bypass RT: status tetap MENUNGGU_RT dan redirect dengan flash error)
     */
    public function test_branch_rw_direct_approval_from_menunggu_rt(): void
    {
        $surat = PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Pendaftaran Sekolah'],
        ]);

        $response = $this->actingAs($this->rwUser)->post("/surat/{$surat->id}/process", [
            'action' => 'approve',
        ]);

        $response->assertRedirect(route('surat.show', $surat->id));
        $response->assertSessionHas('error');
        $surat->refresh();
        $this->assertEquals('MENUNGGU_RT', $surat->status);
    }

    /**
     * White-Box: DashboardController index role-based branching
     */
    public function test_branch_dashboard_renders_for_all_four_roles(): void
    {
        // 1. Super Admin branch
        $resAdmin = $this->actingAs($this->superAdmin)->get('/dashboard');
        $resAdmin->assertStatus(200);
        $resAdmin->assertViewHas('stats');
        $this->assertArrayHasKey('total_rw', $resAdmin->viewData('stats'));

        // 2. RW branch
        $resRw = $this->actingAs($this->rwUser)->get('/dashboard');
        $resRw->assertStatus(200);
        $resRw->assertViewHas('stats');
        $this->assertArrayHasKey('total_rt', $resRw->viewData('stats'));

        // 3. RT branch
        $resRt = $this->actingAs($this->rtUser)->get('/dashboard');
        $resRt->assertStatus(200);
        $resRt->assertViewHas('stats');
        $this->assertArrayHasKey('total_warga', $resRt->viewData('stats'));

        // 4. Warga branch
        $resWarga = $this->actingAs($this->wargaUser)->get('/dashboard');
        $resWarga->assertStatus(200);
        $resWarga->assertViewHas('stats');
        $this->assertArrayHasKey('pengajuan_saya', $resWarga->viewData('stats'));
    }
}
