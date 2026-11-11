<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $rwUser;

    private User $rtUser;

    private User $wargaUser;

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
        $this->wargaUser = User::factory()->create(['role_id' => $roleWarga->id, 'rw_id' => $rw->id, 'rt_id' => $rt->id]);
    }

    public function test_guest_is_redirected_to_login_for_protected_routes(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/desa')->assertRedirect('/login');
        $this->get('/users')->assertRedirect('/login');
        $this->get('/rw')->assertRedirect('/login');
        $this->get('/rt')->assertRedirect('/login');
        $this->get('/warga')->assertRedirect('/login');
        $this->get('/surat')->assertRedirect('/login');
    }

    public function test_super_admin_has_full_access_to_administrative_modules(): void
    {
        $this->actingAs($this->superAdmin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/desa')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/users')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/rw')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/rt')->assertStatus(200);
        $this->actingAs($this->superAdmin)->get('/warga')->assertStatus(200);
    }

    public function test_rw_user_forbidden_from_desa_and_users_management(): void
    {
        $this->actingAs($this->rwUser)->get('/desa')->assertStatus(403);
        $this->actingAs($this->rwUser)->get('/users')->assertStatus(403);
        $this->actingAs($this->rwUser)->get('/rw')->assertStatus(200);
        $this->actingAs($this->rwUser)->get('/rt')->assertStatus(200);
    }

    public function test_rt_user_forbidden_from_desa_users_and_rw_management(): void
    {
        $this->actingAs($this->rtUser)->get('/desa')->assertStatus(403);
        $this->actingAs($this->rtUser)->get('/users')->assertStatus(403);
        $this->actingAs($this->rtUser)->get('/rw')->assertStatus(403);
        $this->actingAs($this->rtUser)->get('/rt')->assertStatus(200);
    }

    public function test_warga_forbidden_from_admin_and_wilayah_management(): void
    {
        $this->actingAs($this->wargaUser)->get('/desa')->assertStatus(403);
        $this->actingAs($this->wargaUser)->get('/users')->assertStatus(403);
        $this->actingAs($this->wargaUser)->get('/rw')->assertStatus(403);
        $this->actingAs($this->wargaUser)->get('/rt')->assertStatus(403);
        $this->actingAs($this->wargaUser)->get('/warga')->assertStatus(200);
    }
}
