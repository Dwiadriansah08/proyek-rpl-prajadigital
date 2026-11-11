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

class RwRtCascadeProtectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Desa $desa;

    private Rw $rw;

    private Rt $rt;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'super_admin']);
        $this->admin = User::factory()->create(['role_id' => $roleAdmin->id]);

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
    }

    public function test_cannot_delete_rw_when_has_associated_rt(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('rw.destroy', $this->rw->id));

        $response->assertRedirect(route('rw.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rw', ['id' => $this->rw->id]);
    }

    public function test_cannot_delete_rt_when_has_associated_warga(): void
    {
        Warga::create([
            'nik' => '3201019999990001',
            'nomor_kk' => '3201019999990002',
            'nama_lengkap' => 'Warga RT Test',
            'tempat_lahir' => 'Kota A',
            'tanggal_lahir' => '1995-05-05',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pendidikan' => 'S1',
            'pekerjaan' => 'Karyawan',
            'alamat' => 'Jl. Mawar',
            'rw_id' => $this->rw->id,
            'rt_id' => $this->rt->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('rt.destroy', $this->rt->id));

        $response->assertRedirect(route('rt.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('rt', ['id' => $this->rt->id]);
    }

    public function test_can_delete_empty_rt_and_empty_rw(): void
    {
        // 1. Delete empty RT
        $resRt = $this->actingAs($this->admin)->delete(route('rt.destroy', $this->rt->id));
        $resRt->assertRedirect(route('rt.index'));
        $resRt->assertSessionHas('success');
        $this->assertDatabaseMissing('rt', ['id' => $this->rt->id]);

        // 2. Now RW has no RTs and no Wargas, so it can be deleted
        $resRw = $this->actingAs($this->admin)->delete(route('rw.destroy', $this->rw->id));
        $resRw->assertRedirect(route('rw.index'));
        $resRw->assertSessionHas('success');
        $this->assertDatabaseMissing('rw', ['id' => $this->rw->id]);
    }
}
