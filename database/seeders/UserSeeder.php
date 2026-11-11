<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $roleAdminDesa = Role::firstOrCreate(['name' => 'admin_desa']);
        $roleRw = Role::firstOrCreate(['name' => 'rw']);
        $roleRt = Role::firstOrCreate(['name' => 'rt']);
        $roleWarga = Role::firstOrCreate(['name' => 'warga']);

        $desa = Desa::first();
        if (! $desa) {
            $desa = Desa::create([
                'nama' => 'Desa Sukamaju',
                'kecamatan' => 'Kecamatan Makmur',
                'kabupaten_kota' => 'Kabupaten Sejahtera',
                'provinsi' => 'Provinsi Gemilang',
                'alamat' => 'Jl. Merdeka No. 1',
                'kode_pos' => '12345',
            ]);
        }

        $rw = Rw::first();
        if (! $rw) {
            $rw = Rw::create([
                'desa_id' => $desa->id,
                'nomor_rw' => '001',
                'ketua_rw' => 'Yono',
                'alamat' => 'Lingkungan RW 001',
                'status' => true,
            ]);
        } else {
            $rw->update(['ketua_rw' => 'Yono']);
        }

        $rt = Rt::first();
        if (! $rt) {
            $rt = Rt::create([
                'rw_id' => $rw->id,
                'nomor_rt' => '001',
                'ketua_rt' => 'YANTO SUJONO',
                'alamat' => 'Lingkungan RT 001 RW 001',
                'status' => true,
            ]);
        } else {
            $rt->update(['ketua_rt' => 'YANTO SUJONO']);
        }

        // 1. Super Admin: superadmin@prajadigital.desa.id | password (Name: Super Admin)
        User::updateOrCreate(
            ['email' => 'superadmin@prajadigital.desa.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role_id' => $roleSuperAdmin->id,
                'is_active' => true,
            ]
        );

        // 2. Admin Desa: admindesa@desa.com | password (Name: Admin Desa)
        User::updateOrCreate(
            ['email' => 'admindesa@desa.com'],
            [
                'name' => 'Admin Desa',
                'password' => Hash::make('password'),
                'role_id' => $roleAdminDesa->id,
                'desa_id' => $desa?->id,
                'is_active' => true,
            ]
        );

        // 3. Ketua RW: rw01@desa.com | password (Name: Yono)
        User::updateOrCreate(
            ['email' => 'rw01@desa.com'],
            [
                'name' => 'Yono',
                'password' => Hash::make('password'),
                'role_id' => $roleRw->id,
                'desa_id' => $rw?->desa_id ?? $desa?->id,
                'rw_id' => $rw?->id,
                'is_active' => true,
            ]
        );

        // 4. Ketua RT: rt01@desa.com | password (Name: YANTO SUJONO)
        User::updateOrCreate(
            ['email' => 'rt01@desa.com'],
            [
                'name' => 'YANTO SUJONO',
                'password' => Hash::make('password'),
                'role_id' => $roleRt->id,
                'desa_id' => $rt?->rw?->desa_id ?? $desa?->id,
                'rw_id' => $rt?->rw_id ?? $rw?->id,
                'rt_id' => $rt?->id,
                'is_active' => true,
            ]
        );

        // 5. Warga: dwiadriansah4@gmail.com | password (Name: dwi adriansah)
        $warga = Warga::where('email', 'dwiadriansah4@gmail.com')->first();
        if (! $warga) {
            $warga = Warga::create([
                'nik' => '3201010101010099',
                'nomor_kk' => '3201010101010088',
                'nama_lengkap' => 'dwi adriansah',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1995-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Kawin',
                'pendidikan' => 'S1',
                'pekerjaan' => 'Wiraswasta',
                'alamat' => $rt?->alamat ?? 'Lingkungan RT 001 RW 001',
                'rw_id' => $rw?->id,
                'rt_id' => $rt?->id,
                'status_dalam_keluarga' => 'Kepala Keluarga',
                'status_kependudukan' => 'Tetap',
                'email' => 'dwiadriansah4@gmail.com',
                'status_aktif' => true,
            ]);
        } else {
            $warga->update([
                'nama_lengkap' => 'dwi adriansah',
                'rw_id' => $rw?->id ?? $warga->rw_id,
                'rt_id' => $rt?->id ?? $warga->rt_id,
                'status_aktif' => true,
            ]);
        }

        User::updateOrCreate(
            ['email' => 'dwiadriansah4@gmail.com'],
            [
                'name' => 'dwi adriansah',
                'password' => Hash::make('password'),
                'role_id' => $roleWarga->id,
                'desa_id' => $desa?->id,
                'rw_id' => $warga->rw_id ?? $rw?->id,
                'rt_id' => $warga->rt_id ?? $rt?->id,
                'is_active' => true,
            ]
        );
    }
}
