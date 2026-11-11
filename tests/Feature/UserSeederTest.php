<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_all_five_official_demo_accounts_with_correct_credentials(): void
    {
        // Setup preliminary geographic structure
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
            'ketua_rw' => 'Ketua RW Awal',
            'status' => true,
        ]);

        $rt = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT Awal',
            'status' => true,
        ]);

        // Run Seeder
        $this->seed(UserSeeder::class);

        // 1. Super Admin
        $superAdmin = User::where('email', 'superadmin@prajadigital.desa.id')->first();
        $this->assertNotNull($superAdmin, 'Akun Super Admin harus berhasil di-seed.');
        $this->assertEquals('Super Admin', $superAdmin->name);
        $this->assertTrue(Hash::check('password', $superAdmin->password));
        $this->assertEquals('super_admin', $superAdmin->role->name);
        $this->assertTrue($superAdmin->is_active);

        // 2. Admin Desa
        $adminDesa = User::where('email', 'admindesa@desa.com')->first();
        $this->assertNotNull($adminDesa, 'Akun Admin Desa harus berhasil di-seed.');
        $this->assertEquals('Admin Desa', $adminDesa->name);
        $this->assertTrue(Hash::check('password', $adminDesa->password));
        $this->assertEquals('admin_desa', $adminDesa->role->name);
        $this->assertTrue($adminDesa->is_active);

        // 3. Ketua RW
        $ketuaRw = User::where('email', 'rw01@desa.com')->first();
        $this->assertNotNull($ketuaRw, 'Akun Ketua RW harus berhasil di-seed.');
        $this->assertEquals('Yono', $ketuaRw->name);
        $this->assertTrue(Hash::check('password', $ketuaRw->password));
        $this->assertEquals('rw', $ketuaRw->role->name);
        $this->assertTrue($ketuaRw->is_active);
        $this->assertEquals('Yono', $rw->fresh()->ketua_rw);

        // 4. Ketua RT
        $ketuaRt = User::where('email', 'rt01@desa.com')->first();
        $this->assertNotNull($ketuaRt, 'Akun Ketua RT harus berhasil di-seed.');
        $this->assertEquals('YANTO SUJONO', $ketuaRt->name);
        $this->assertTrue(Hash::check('password', $ketuaRt->password));
        $this->assertEquals('rt', $ketuaRt->role->name);
        $this->assertTrue($ketuaRt->is_active);
        $this->assertEquals('YANTO SUJONO', $rt->fresh()->ketua_rt);

        // 5. Warga
        $wargaUser = User::where('email', 'dwiadriansah4@gmail.com')->first();
        $this->assertNotNull($wargaUser, 'Akun Warga harus berhasil di-seed.');
        $this->assertEquals('dwi adriansah', $wargaUser->name);
        $this->assertTrue(Hash::check('password', $wargaUser->password));
        $this->assertEquals('warga', $wargaUser->role->name);
        $this->assertTrue($wargaUser->is_active);
    }

    public function test_user_seeder_is_idempotent(): void
    {
        $this->seed(UserSeeder::class);
        $firstCount = User::count();

        // Run again should not create duplicate users or throw errors
        $this->seed(UserSeeder::class);
        $secondCount = User::count();

        $this->assertEquals($firstCount, $secondCount, 'UserSeeder harus bersifat idempoten tanpa duplikasi akun.');
    }
}
