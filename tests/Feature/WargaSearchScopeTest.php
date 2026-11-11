<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WargaSearchScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TDD Demonstration Test:
     * An RT user must ONLY see residents from their own RT,
     * even when searching with a search term that matches residents in other RTs.
     */
    public function test_rt_user_search_cannot_leak_warga_from_other_rt(): void
    {
        $roleRt = Role::create(['name' => 'rt']);

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

        $rt1 = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'status' => true,
        ]);

        $rt2 = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '002',
            'ketua_rt' => 'Ketua RT 2',
            'alamat' => 'Lingkungan RT 2 RW 1',
            'status' => true,
        ]);

        // Ketua RT 1
        $userRt1 = User::factory()->create([
            'role_id' => $roleRt->id,
            'rw_id' => $rw->id,
            'rt_id' => $rt1->id,
            'is_active' => true,
        ]);

        // Warga in RT 1
        $wargaRt1 = Warga::create([
            'nik' => '3201011111110001',
            'nomor_kk' => '3201011111110002',
            'nama_lengkap' => 'Ahmad Warga RT1',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'RT 1',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'rw_id' => $rw->id,
            'rt_id' => $rt1->id,
        ]);

        // Warga in RT 2 (different RT)
        $wargaRt2 = Warga::create([
            'nik' => '3201012222220001',
            'nomor_kk' => '3201012222220002',
            'nama_lengkap' => 'Budi Warga RT2',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1992-02-02',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Karyawan',
            'alamat' => 'RT 2',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'rw_id' => $rw->id,
            'rt_id' => $rt2->id,
        ]);

        // Ketua RT 1 searches by NIK of Warga RT 2
        $response = $this->actingAs($userRt1)->get('/warga?search=3201012222220001');

        $response->assertStatus(200);

        // The warga from RT 2 MUST NOT be present in the view data
        $wargaList = $response->viewData('warga');
        $this->assertCount(0, $wargaList, 'Warga dari RT 2 tidak boleh bocor ke Ketua RT 1 saat melakukan pencarian!');
    }

    public function test_rt_user_search_finds_warga_within_own_rt(): void
    {
        $roleRt = Role::create(['name' => 'rt']);
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
        $rt1 = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1',
            'alamat' => 'Lingkungan RT 1 RW 1',
            'status' => true,
        ]);

        $userRt1 = User::factory()->create([
            'role_id' => $roleRt->id,
            'rw_id' => $rw->id,
            'rt_id' => $rt1->id,
            'is_active' => true,
        ]);

        Warga::create([
            'nik' => '3201011111110001',
            'nomor_kk' => '3201011111110002',
            'nama_lengkap' => 'Ahmad Warga RT1',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'SMA',
            'pekerjaan' => 'Wiraswasta',
            'alamat' => 'RT 1',
            'status_dalam_keluarga' => 'Kepala Keluarga',
            'status_kependudukan' => 'Tetap',
            'rw_id' => $rw->id,
            'rt_id' => $rt1->id,
        ]);

        // Ketua RT 1 searches by Ahmad (his own resident)
        $response = $this->actingAs($userRt1)->get('/warga?search=Ahmad');

        $response->assertStatus(200);
        $wargaList = $response->viewData('warga');
        $this->assertCount(1, $wargaList);
        $this->assertEquals('Ahmad Warga RT1', $wargaList->first()->nama_lengkap);
    }
}
