<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Models\Keluarga;
use App\Models\Warga;
use App\Services\VillageStatistikService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$svc = new VillageStatistikService;

echo '=== SKENARIO: Warga dihapus tapi nomor_kk masih di tabel keluarga ==='.PHP_EOL;

// Simulasikan: insert KK ke tabel keluarga dengan nomor KK warga 34
DB::beginTransaction();
try {
    // Simulasi: ada record di keluarga dengan nomor_kk yang sama dengan warga 34
    $kkWarga34 = '5555555555555555';
    DB::table('keluarga')->insert([
        'nomor_kk' => $kkWarga34,
        'kepala_keluarga' => 'DONO',
        'alamat' => 'Test',
        'rw_id' => 1,
        'rt_id' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Keluarga inserted with nomor_kk: $kkWarga34".PHP_EOL;

    echo PHP_EOL.'=== SEBELUM DELETE warga 34 ==='.PHP_EOL;
    $kk = $svc->getKkStats();
    echo 'total_kk: '.$kk['total_kk'].' (expected 2 - kk unik: 444.. dan 555..)'.PHP_EOL;

    // Delete warga 34
    $warga34 = Warga::find(34);
    $warga34->delete();
    echo PHP_EOL.'Warga 34 dihapus'.PHP_EOL;

    echo PHP_EOL.'=== SETELAH DELETE warga 34 (tapi nomor_kk masih ada di keluarga) ==='.PHP_EOL;
    echo 'Warga count: '.Warga::count().PHP_EOL;
    echo 'Keluarga count: '.Keluarga::count().PHP_EOL;
    $kk2 = $svc->getKkStats();
    echo 'total_kk: '.$kk2['total_kk'].' (masalah: harusnya turun jika KK tidak punya anggota)'.PHP_EOL;
    echo 'Penjelasan: Kalau KK masih ada di tabel keluarga, KK dihitung TETAP ada meski warganya dihapus'.PHP_EOL;
    echo 'unique_nomor_kk: '.implode(', ', $kk2['unique_nomor_kk']).PHP_EOL;

} finally {
    DB::rollBack();
    echo PHP_EOL.'ROLLBACK selesai'.PHP_EOL;
}

echo PHP_EOL.'=== KESIMPULAN ==='.PHP_EOL;
echo "Bug terjadi jika: warga dihapus TAPI nomor_kk-nya masih tercatat di tabel 'keluarga'.".PHP_EOL;
echo 'Akibat: getKkStats() masih menghitung KK itu karena concat dari warga+keluarga.'.PHP_EOL;
echo 'Perbaikan: Saat warga kepala keluarga dihapus, harus cek apakah KK-nya masih valid.'.PHP_EOL;
echo 'Skema aktual: keluarga table kosong (0 rows), jadi skenario ini BELUM terjadi sekarang.'.PHP_EOL;
