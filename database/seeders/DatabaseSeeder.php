<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratTemplate;
use App\Models\Warga;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = ['super_admin', 'admin_desa', 'rw', 'rt', 'warga'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
        $roleSuperAdmin = Role::where('name', 'super_admin')->first();
        $roleAdminDesa = Role::where('name', 'admin_desa')->first();
        $roleRw = Role::where('name', 'rw')->first();
        $roleRt = Role::where('name', 'rt')->first();
        $roleWarga = Role::where('name', 'warga')->first();

        // 2. Desa
        $desa = Desa::create([
            'nama' => 'Desa Sukamaju',
            'kecamatan' => 'Kecamatan Makmur',
            'kabupaten_kota' => 'Kabupaten Sejahtera',
            'provinsi' => 'Provinsi Gemilang',
            'alamat' => 'Jl. Merdeka No. 1',
            'kode_pos' => '12345',
            'kepala_desa' => 'Bapak Budi Santoso',
            'deskripsi' => 'Desa percontohan digital',
        ]);

        // 3. RW
        for ($i = 1; $i <= 3; $i++) {
            $rw = Rw::create([
                'desa_id' => $desa->id,
                'nomor_rw' => sprintf('%03d', $i),
                'ketua_rw' => $i === 1 ? 'Yono' : 'Ketua RW '.$i,
                'alamat' => 'Lingkungan RW '.$i,
                'status' => true,
            ]);

            // 4. RT
            for ($j = 1; $j <= 2; $j++) {
                $rt = Rt::create([
                    'rw_id' => $rw->id,
                    'nomor_rt' => sprintf('%03d', $j),
                    'ketua_rt' => ($i === 1 && $j === 1) ? 'YANTO SUJONO' : 'Ketua RT '.$j,
                    'alamat' => 'Lingkungan RT '.$j.' RW '.$i,
                    'status' => true,
                ]);

                // 5. Warga
                for ($k = 1; $k <= 5; $k++) {
                    Warga::create([
                        'nik' => '320'.$i.$j.rand(10000000000, 99999999999),
                        'nomor_kk' => '320'.$i.$j.rand(10000000000, 99999999999),
                        'nama_lengkap' => 'Warga '.$i.$j.$k,
                        'tempat_lahir' => 'Kota A',
                        'tanggal_lahir' => '1990-01-01',
                        'jenis_kelamin' => $k % 2 == 0 ? 'Perempuan' : 'Laki-laki',
                        'agama' => 'Islam',
                        'status_perkawinan' => 'Belum Kawin',
                        'pendidikan' => 'SMA',
                        'pekerjaan' => 'Wiraswasta',
                        'alamat' => $rt->alamat,
                        'rw_id' => $rw->id,
                        'rt_id' => $rt->id,
                        'status_dalam_keluarga' => 'Kepala Keluarga',
                        'status_kependudukan' => 'Tetap',
                    ]);
                }
            }
        }

        // 5. Surat Templates
        SuratTemplate::create([
            'nama_surat' => 'Surat Pengantar RT/RW',
            'kode_surat' => 'SK-01',
            'deskripsi' => 'Surat Pengantar Umum dari RT/RW',
            'is_active' => true,
        ]);
        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Domisili',
            'kode_surat' => 'SK-02',
            'deskripsi' => 'Surat Keterangan Domisili Warga',
            'is_active' => true,
        ]);
        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Tidak Mampu',
            'kode_surat' => 'SK-03',
            'deskripsi' => 'Surat Keterangan Tidak Mampu (SKTM)',
            'is_active' => true,
        ]);

        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Kematian',
            'kode_surat' => 'SK-04',
            'deskripsi' => 'Surat Keterangan Kematian Warga',
            'is_active' => true,
        ]);
        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Usaha',
            'kode_surat' => 'SK-05',
            'deskripsi' => 'Surat Keterangan Usaha (SKU) Mikro/Kecil',
            'is_active' => true,
        ]);
        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Berkelakuan Baik',
            'kode_surat' => 'SK-06',
            'deskripsi' => 'Surat Pengantar SKCK / Kelakuan Baik',
            'is_active' => true,
        ]);
        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Penghasilan',
            'kode_surat' => 'SK-07',
            'deskripsi' => 'Surat Keterangan Rincian/Penghasilan Orang Tua',
            'is_active' => true,
        ]);

        // 6. Demo Users
        $this->call(UserSeeder::class);
    }
}
