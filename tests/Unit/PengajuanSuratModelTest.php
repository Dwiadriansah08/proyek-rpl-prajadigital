<?php

namespace Tests\Unit;

use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratTemplate;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PengajuanSuratModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_tambahan_casts_to_array_and_approved_at_casts_to_datetime(): void
    {
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
        ]);
        $rt = Rt::create([
            'rw_id' => $rw->id,
            'nomor_rt' => '001',
            'ketua_rt' => 'Ketua RT 1',
            'alamat' => 'Lingkungan RT 1 RW 1',
        ]);
        $warga = Warga::create([
            'nik' => '3201011111111111',
            'nomor_kk' => '3201012222222222',
            'nama_lengkap' => 'Warga Uji',
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
            'rw_id' => $rw->id,
            'rt_id' => $rt->id,
        ]);
        $template = SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Usaha',
            'kode_surat' => 'SKU-01',
            'deskripsi' => 'Surat Keterangan Usaha Warga',
            'is_active' => true,
        ]);

        $payload = ['nama_usaha' => 'Toko Kelontong', 'keperluan' => 'Pengajuan KUR'];
        $now = now();

        $surat = PengajuanSurat::create([
            'warga_id' => $warga->id,
            'template_id' => $template->id,
            'status' => 'MENUNGGU_RT',
            'data_tambahan' => $payload,
            'approved_at' => $now,
        ]);

        $surat->refresh();

        $this->assertIsArray($surat->data_tambahan);
        $this->assertEquals('Toko Kelontong', $surat->data_tambahan['nama_usaha']);
        $this->assertInstanceOf(Carbon::class, $surat->approved_at);
        $this->assertEquals($now->toDateTimeString(), $surat->approved_at->toDateTimeString());
    }
}
