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

class BlackBoxValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $rtUser;

    private User $wargaUser;

    private Rw $rw;

    private Rt $rt;

    private SuratTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->rtUser = User::factory()->create([
            'role_id' => $roleRt->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $wargaModel = Warga::create([
            'nik' => '3201019999990001',
            'nomor_kk' => '3201019999990002',
            'nama_lengkap' => 'Warga Pengaju',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1995-05-05',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Karyawan',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
        ]);

        $this->wargaUser = User::factory()->create([
            'email' => 'warga.pengaju@example.com',
            'role_id' => $roleWarga->id,
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);
        $wargaModel->update(['email' => $this->wargaUser->email]);

        $this->template = SuratTemplate::create([
            'nama_surat' => 'Surat Pengantar SKCK',
            'kode_surat' => 'SKCK-01',
            'deskripsi' => 'Pengantar SKCK',
            'is_active' => true,
        ]);
    }

    /**
     * BVA & EP: Warga NIK validation tests
     */
    public function test_bva_warga_nik_rejects_15_digits_boundary_below(): void
    {
        $response = $this->actingAs($this->rtUser)->post('/warga', [
            'nik' => '320101123456789', // 15 digits
            'nomor_kk' => '3201011234567890',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response->assertSessionHasErrors('nik');
    }

    public function test_bva_warga_nik_rejects_17_digits_boundary_above(): void
    {
        $response = $this->actingAs($this->rtUser)->post('/warga', [
            'nik' => '32010112345678901', // 17 digits
            'nomor_kk' => '3201011234567890',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'Laki-laki',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response->assertSessionHasErrors('nik');
    }

    public function test_bva_warga_nik_accepts_valid_16_digits_exact_boundary(): void
    {
        $response = $this->actingAs($this->rtUser)->post('/warga', [
            'nik' => '3201011234567890', // exactly 16 digits
            'nomor_kk' => '3201011234567890',
            'nama_lengkap' => 'Budi Santoso',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('warga', ['nik' => '3201011234567890']);
    }

    public function test_ep_warga_nik_rejects_duplicate_entry(): void
    {
        $response = $this->actingAs($this->rtUser)->post('/warga', [
            'nik' => '3201019999990001', // duplicate of existing warga
            'nomor_kk' => '3201011234567890',
            'nama_lengkap' => 'Cloned NIK',
            'jenis_kelamin' => 'Laki-laki',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response->assertSessionHasErrors('nik');
    }

    /**
     * EP: Jenis Kelamin enum validation tests
     */
    public function test_ep_jenis_kelamin_rejects_unlisted_values(): void
    {
        $response = $this->actingAs($this->rtUser)->post('/warga', [
            'nik' => '3201018888880001',
            'nomor_kk' => '3201018888880002',
            'nama_lengkap' => 'Warga Test',
            'jenis_kelamin' => 'Pria', // invalid value (must be Laki-laki or Perempuan)
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response->assertSessionHasErrors('jenis_kelamin');
    }

    /**
     * BVA: Nama Lengkap max 255 validation
     */
    public function test_bva_nama_lengkap_rejects_256_characters(): void
    {
        $response = $this->actingAs($this->rtUser)->post('/warga', [
            'nik' => '3201017777770001',
            'nomor_kk' => '3201017777770002',
            'nama_lengkap' => str_repeat('A', 256), // 256 characters (exceeds max 255)
            'jenis_kelamin' => 'Laki-laki',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response->assertSessionHasErrors('nama_lengkap');
    }

    /**
     * EP: Pengajuan Surat input validation
     */
    public function test_ep_pengajuan_surat_rejects_missing_keperluan(): void
    {
        $response = $this->actingAs($this->wargaUser)->post('/surat', [
            'template_id' => $this->template->id,
            'keperluan' => '', // empty string
        ]);

        $response->assertSessionHasErrors('keperluan');
    }

    public function test_ep_pengajuan_surat_rejects_nonexistent_template_id(): void
    {
        $response = $this->actingAs($this->wargaUser)->post('/surat', [
            'template_id' => 999999, // non-existent template
            'keperluan' => 'Untuk keperluan beasiswa',
        ]);

        $response->assertSessionHasErrors('template_id');
    }

    /**
     * EP: Pengajuan Surat Process action validation
     */
    public function test_ep_process_surat_rejects_invalid_action_value(): void
    {
        $surat = PengajuanSurat::create([
            'warga_id' => $this->wargaUser->warga->id,
            'template_id' => $this->template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => ['keperluan' => 'Uji Coba'],
        ]);

        $response = $this->actingAs($this->rtUser)->post("/surat/{$surat->id}/process", [
            'action' => 'pending', // invalid action (only approve or reject)
        ]);

        $response->assertSessionHasErrors('action');
    }
}
