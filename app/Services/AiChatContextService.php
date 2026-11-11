<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Desa;
use App\Models\Informasi;
use App\Models\Keluarga;
use App\Models\PengajuanSurat;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\SuratNotification;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Models\Warga;
use App\Models\WhatsappBroadcast;

/**
 * Service untuk mengambil konteks data terotorisasi dari database aplikasi
 * khusus untuk kebutuhan AI Chatbot Sistem Informasi Desa.
 *
 * Menerapkan prinsip:
 * 1. Otorisasi backend ketat berbasis role & relasi wilayah user.
 * 2. Hanya mengambil potongan data yang relevan dengan pertanyaan user (intent-based).
 * 3. Tidak mengizinkan query SQL bebas.
 * 4. Memisahkan data factual database dari penyusunan bahasa AI.
 */
class AiChatContextService
{
    public function __construct(
        protected ?VillageStatistikService $villageStatistikService = null
    ) {
        $this->villageStatistikService = $villageStatistikService ?? app(VillageStatistikService::class);
    }

    /**
     * Mengambil paket konteks data yang relevan untuk user dan pertanyaannya.
     *
     * @return array{
     *     user_profile: array,
     *     desa_info: ?array,
     *     kependudukan: ?array,
     *     surat_templates: array,
     *     pengajuan_status: ?array,
     *     informasi_terbaru: array,
     *     intent: string
     * }
     */
    public function getContextForMessage(User $user, string $message): array
    {
        $roleName = $user->role ? strtolower($user->role->name) : 'warga';
        $isSuperAdmin = ($roleName === 'super_admin');
        $isVillageAdmin = in_array($roleName, ['super_admin', 'admin_desa']);
        $intent = $this->detectIntent($message);

        return [
            'intent' => $intent,
            'user_profile' => $this->getUserProfileData($user, $roleName),
            'live_master_snapshot' => $isVillageAdmin ? $this->getLiveMasterSnapshot() : null,
            'keyword_lookup' => $this->dynamicKeywordLookup($message, $user, $roleName),
            'desa_info' => $this->getDesaInfo($intent),
            'kependudukan' => $this->getKependudukanData($user, $roleName, $intent),
            'surat_templates' => $this->getRelevantTemplates($intent, $message),
            'pengajuan_status' => $this->getPengajuanStatus($user, $roleName, $intent),
            'statistik_surat' => $this->getSuratStatistikData($user, $roleName, $intent),
            'statistik_wilayah' => $this->getWilayahStatistikData($user, $roleName, $intent),
            'statistik_akun' => $this->getAkunStatistikData($user, $roleName, $intent),
            'statistik_kk' => $this->getKepalaKeluargaStatistikData($user, $roleName, $intent),
            'layanan_surat' => $this->getLayananSuratData($intent),
            'informasi_terbaru' => $this->getInformasiTerbaru($user, $roleName, $intent),
            'data_akun' => $this->getAkunPenggunaData($user, $roleName, $intent),
            'daftar_warga' => $this->getDaftarWargaData($user, $roleName, $intent),
            'ketua_wilayah' => $this->getKetuaWilayahData($user, $roleName, $isSuperAdmin ? 'ketua_wilayah' : $intent),
            'notifikasi_data' => $this->getNotifikasiData($user, $roleName, $intent),
            'whatsapp_data' => $this->getWhatsappLogData($user, $roleName, $intent),
            'aktivitas_data' => $this->getAktivitasLogData($user, $roleName, $intent),
            'panduan_fitur_data' => $this->getFeatureGuideData($user, $roleName, $intent),
        ];
    }

    /**
     * Normalisasi string pesan pengguna: huruf kecil, trim, standardisasi singkatan, dan buang tanda baca akhir.
     */
    public function normalizeQuery(string $message): string
    {
        $msg = mb_strtolower(trim($message));
        // Standardisasi singkatan umum di Indonesia
        $msg = preg_replace('/\br\.t\.\b/i', 'rt', $msg);
        $msg = preg_replace('/\br\.w\.\b/i', 'rw', $msg);
        $msg = preg_replace('/\bk\.k\.\b/i', 'kk', $msg);
        $msg = preg_replace('/\bbrp\b/i', 'berapa', $msg);
        $msg = preg_replace('/\byg\b/i', 'yang', $msg);
        $msg = preg_replace('/\bselese\b/i', 'selesai', $msg);
        $msg = preg_replace('/\bberes\b/i', 'selesai', $msg);
        $msg = preg_replace('/\bacc\b/i', 'disetujui', $msg);
        $msg = preg_replace('/\bnotif\b/i', 'notifikasi', $msg);
        $msg = preg_replace('/\bdi\s+proses\b/i', 'diproses', $msg);
        $msg = preg_replace('/\bsedang\s+di\s+proses\b/i', 'sedang diproses', $msg);
        $msg = preg_replace('/\bdi\s+setujui\b/i', 'disetujui', $msg);
        $msg = preg_replace('/\bdi\s+tolak\b/i', 'ditolak', $msg);

        // Hapus tanda baca di ujung teks (? ! . , ; : _ " ')
        return trim(preg_replace('/[?!.,;:_"\']+/u', ' ', $msg));
    }

    /**
     * Mendeteksi maksud/intent pesan pengguna secara aman dan terstruktur.
     */
    public function detectIntent(string $message): string
    {
        $clean = $this->normalizeQuery($message);

        // 1. Intent deteksi ketua RT / RW / Kepala Desa / Pengurus
        if (preg_match('/(siapa.*(ketua|kades|kepala desa|rw|rt|pengurus)|nama.*(ketua|kades|kepala desa|rt|rw|pengurus)|ketua.*(rt|rw)|kontak.*(rt|rw|ketua)|pengurus.*(rt|rw)|(rt|rw).*(siapa|pengurus|ketua))/i', $clean)) {
            return 'ketua_wilayah';
        }

        // 2. Intent daftar nama warga / kependudukan (bukan sekadar hitungan/jumlah)
        if (preg_match('/(daftar|siapa saja|nama-nama|list|rincian nama).*(warga|penduduk|orang)/i', $clean) ||
            preg_match('/(siapa saja warga|daftar warga|daftar penduduk|siapa saja penduduk)/i', $clean)) {
            return 'daftar_warga';
        }

        // 3. Status pengajuan surat saya & verifikasi surat petugas
        if (preg_match('/(status.*surat|cek.*status|pengajuan saya|status pengajuan|permohonan saya)/i', $clean)) {
            return 'status_pengajuan';
        }

        if (preg_match('/(butuh.*(persetujuan|tindakan|verifikasi|approval)|perlu.*(verifikasi|tindakan|disetujui)|antrean.*surat|pengajuan.*(masuk|baru|tertunda|pending))/i', $clean)) {
            return 'verifikasi_surat';
        }

        // 3b. Statistik & Rekapitulasi Permohonan / Layanan Surat (diproses, pending, selesai, disetujui, ditolak, total permohonan/berkas/surat)
        if (preg_match('/(berapa.*(surat|pengajuan|permohonan|berkas)|jumlah.*(surat|pengajuan|permohonan|berkas)|total.*(surat|pengajuan|permohonan|berkas)|statistik.*(surat|pengajuan|permohonan|berkas)|rekap.*(surat|pengajuan|permohonan|berkas))/i', $clean) ||
            preg_match('/(surat|pengajuan|permohonan|berkas).*(selesai|disetujui|menunggu|ditolak|diproses|pending|proses|berapa|jumlah|total|statistik|rekap)/i', $clean) ||
            preg_match('/(sudah|yang|lagi|sedang|masih).*(selesai|disetujui|ditolak|menunggu|diproses|pending|proses)/i', $clean) ||
            preg_match('/\b(diproses|sedang diproses|pending|total surat|total berkas|permohonan surat|rekap surat|total permohonan)\b/i', $clean)) {
            return 'statistik_surat';
        }

        // 4. Statistik Akun berdasarkan role (super_admin, rw, rt, warga) atau total akun pengguna
        // HARUS dievaluasi sebelum statistik wilayah agar "user role rw" tidak tertukar dengan wilayah RW
        if (preg_match('/\b(user|akun)\b.*\b(role|dengan role)\b/i', $clean) ||
            preg_match('/\b(role)\b.*\b(user|akun)\b/i', $clean) ||
            preg_match('/(berapa|jumlah|total|ada berapa).*(akun|user)\b/i', $clean) ||
            preg_match('/\b(total|jumlah|berapa)\s+(akun|user)\s+(rw|rt|warga|admin|super_admin)\b/i', $clean) ||
            preg_match('/\b(akun|user)\s+(rw|rt|warga|admin|super_admin)\s+(ada berapa|berapa|totalnya)\b/i', $clean) ||
            preg_match('/(akun.*(pengguna|user|warga|rt|rw|admin)|user.*(aktif|terdaftar|role)|data.*(akun|user))/i', $clean)) {
            return 'statistik_akun';
        }

        // 5. Statistik Penduduk / Kependudukan (jumlah warga riil desa, bukan akun login)
        if (preg_match('/(jumlah.*(warga|penduduk|orang|jiwa)|berapa.*(warga|penduduk|orang|jiwa)|total.*(warga|penduduk|orang|jiwa)|statistik.*(warga|penduduk|kependudukan)|data.*(warga|penduduk|kependudukan))/i', $clean)) {
            return 'statistik_penduduk';
        }

        // 6. Statistik Kepala Keluarga / Kartu Keluarga (KK)
        if (preg_match('/(berapa|jumlah|total|ada berapa).*(kepala keluarga|kk|kartu keluarga)/i', $clean) ||
            preg_match('/(kepala keluarga|kk|kartu keluarga).*(ada berapa|berapa|jumlahnya|totalnya)/i', $clean)) {
            return 'statistik_kepala_keluarga';
        }

        // 7. Statistik Wilayah (jumlah RW dan RT fisik administratif desa)
        if (preg_match('/(berapa|jumlah|total|ada berapa).*(jumlah\s+)?\b(rw|rt)\b(?!\s+(surat|pengajuan|warga|penduduk|user|akun))/i', $clean) ||
            preg_match('/\b(rw|rt)\b.*(ada berapa|berapa|jumlahnya|totalnya)\b(?!\s+(user|akun))/i', $clean) ||
            preg_match('/(berapa|ada berapa)\s+(rt|rw)\s+(di desa|seluruh desa|di wilayah)/i', $clean) ||
            preg_match('/(jumlah|total|rekap|statistik)\s+(wilayah|lingkungan)\b/i', $clean)) {
            return 'statistik_wilayah';
        }

        // 8. Jenis layanan surat & template yang tersedia
        if (preg_match('/(layanan surat|jenis surat|daftar surat|template surat|surat apa saja|berapa jenis.*surat|ada berapa jenis.*surat)/i', $clean)) {
            return 'layanan_surat';
        }

        // 9. Notifikasi In-App Surat
        if (preg_match('/(notifikasi|pemberitahuan|notif|pesan masuk)/i', $clean)) {
            return 'notifikasi';
        }

        // 10. WhatsApp Broadcast / Fonnte status
        if (preg_match('/\b(whatsapp|wa|fonnte|broadcast)\b|pesan wa|siaran wa/i', $clean)) {
            return 'whatsapp_log';
        }

        // 11. Log Aktivitas / Audit Log
        if (preg_match('/(log.*aktivitas|audit|riwayat.*sistem|rekam jejak|aktivitas.*terbaru)/i', $clean)) {
            return 'aktivitas_log';
        }

        // 12. Panduan Fitur Sistem
        if (preg_match('/(cara pakai|cara menggunakan|panduan|tutorial|fitur|menu apa saja|bisa apa saja|kemampuan)/i', $clean)) {
            return 'panduan_fitur';
        }

        // 13. Prosedur & Syarat Surat
        if (preg_match('/(surat pengantar|domisili)/i', $clean)) {
            return 'surat_domisili';
        }

        if (preg_match('/(cara mengajukan|cara membuat|alur|prosedur|bagaimana cara|langkah|syarat|persyaratan)/i', $clean)) {
            return 'prosedur_surat';
        }

        // 15. Profil & Info Desa
        if (preg_match('/(kantor desa|alamat desa|kontak|kepala desa|informasi desa|profil desa)/i', $clean)) {
            return 'info_desa';
        }

        // 16. Informasi Publik (berita/pengumuman)
        if (preg_match('/(pengumuman|kegiatan|berita|informasi terbaru)/i', $clean)) {
            return 'info_publik';
        }

        // 17. Informasi umum wilayah pengguna
        if (preg_match('/\b(rt|rw|wilayah)\b/i', $clean)) {
            return 'info_wilayah';
        }

        return 'general';
    }

    /**
     * Mengambil profil otorisasi pengguna saat ini (termasuk wilayah RT/RW resmi).
     */
    public function getUserProfileData(User $user, string $roleName): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $roleName,
            'rw_nomor' => null,
            'rt_nomor' => null,
            'warga_id' => null,
            'is_registered_warga' => false,
        ];

        if ($roleName === 'super_admin') {
            $data['scope_description'] = 'Super Admin dengan akses tingkat desa penuh.';

            return $data;
        }

        if ($roleName === 'admin_desa') {
            $data['scope_description'] = 'Admin Desa / Staf Pelayanan Kantor Desa dengan akses administrasi dan verifikasi surat tingkat desa.';

            return $data;
        }

        if ($roleName === 'rw') {
            $rw = $user->rw;
            $data['rw_id'] = $user->rw_id;
            $data['rw_nomor'] = $rw ? $rw->nomor_rw : null;
            $data['scope_description'] = 'Pengurus RW '.($data['rw_nomor'] ?? '-');

            return $data;
        }

        if ($roleName === 'rt') {
            $rw = $user->rw;
            $rt = $user->rt;
            $data['rw_id'] = $user->rw_id;
            $data['rt_id'] = $user->rt_id;
            $data['rw_nomor'] = $rw ? $rw->nomor_rw : null;
            $data['rt_nomor'] = $rt ? $rt->nomor_rt : null;
            $data['scope_description'] = 'Pengurus RT '.($data['rt_nomor'] ?? '-').' RW '.($data['rw_nomor'] ?? '-');

            return $data;
        }

        // Role warga
        $warga = $user->warga;
        if ($warga) {
            $data['warga_id'] = $warga->id;
            $data['is_registered_warga'] = true;
            $data['nama_warga'] = $warga->nama_lengkap;
            $data['rw_id'] = $warga->rw_id;
            $data['rt_id'] = $warga->rt_id;
            $data['rw_nomor'] = $warga->rw?->nomor_rw;
            $data['rt_nomor'] = $warga->rt?->nomor_rt;
            $data['scope_description'] = 'Warga RW '.($data['rw_nomor'] ?? '-').' RT '.($data['rt_nomor'] ?? '-');
        } else {
            $data['scope_description'] = 'Pengguna terdaftar belum terhubung data kependudukan Warga.';
        }

        return $data;
    }

    /**
     * Mengambil informasi Desa resmi dari tabel desa jika relevan.
     */
    public function getDesaInfo(string $intent): ?array
    {
        $desa = Desa::first();
        if (! $desa) {
            return null;
        }

        return [
            'nama' => $desa->nama,
            'kecamatan' => $desa->kecamatan,
            'kabupaten_kota' => $desa->kabupaten_kota,
            'provinsi' => $desa->provinsi,
            'alamat' => $desa->alamat,
            'kode_pos' => $desa->kode_pos,
            'kepala_desa' => $desa->kepala_desa,
            'kontak' => $desa->kontak,
            'deskripsi' => $desa->deskripsi,
        ];
    }

    /**
     * Mengambil template surat resmi yang aktif dari tabel surat_templates.
     */
    public function getRelevantTemplates(string $intent, string $message): array
    {
        $templates = SuratTemplate::where('is_active', true)->get();

        if ($templates->isEmpty()) {
            return [];
        }

        return $templates->map(function (SuratTemplate $t) {
            return [
                'id' => $t->id,
                'nama_surat' => $t->nama_surat,
                'kode_surat' => $t->kode_surat,
                'deskripsi' => $t->deskripsi,
            ];
        })->toArray();
    }

    /**
     * Mengambil data agregat kependudukan / warga resmi sesuai kewenangan dan role user.
     */
    public function getKependudukanData(User $user, string $roleName, string $intent): ?array
    {
        if ($roleName === 'warga') {
            $warga = $user->warga;
            $rtId = $warga?->rt_id ?? $user->rt_id;
            $rwId = $warga?->rw_id ?? $user->rw_id;

            if (! $rtId && ! $rwId) {
                return [
                    'role' => 'warga',
                    'has_data' => false,
                    'message' => 'Akun Anda belum terhubung dengan data wilayah kependudukan RT/RW.',
                ];
            }

            $rt = $warga?->rt ?? ($rtId ? Rt::find($rtId) : null);
            $rw = $warga?->rw ?? ($rwId ? Rw::find($rwId) : null);

            $rtNomor = $rt?->nomor_rt ?? '-';
            $rwNomor = $rw?->nomor_rw ?? '-';
            $countRt = $rtId ? Warga::where('rt_id', $rtId)->count() : 0;
            $countRw = $rwId ? Warga::where('rw_id', $rwId)->count() : 0;

            return [
                'role' => 'warga',
                'has_data' => true,
                'rt_nomor' => $rtNomor,
                'rw_nomor' => $rwNomor,
                'ketua_rt' => $rt ? $this->resolveActiveOfficialName('rt', $rt->id, $rt->ketua_rt) : '-',
                'ketua_rw' => $rw ? $this->resolveActiveOfficialName('rw', $rw->id, $rw->ketua_rw) : '-',
                'jumlah_warga_rt' => $countRt,
                'jumlah_warga_rw' => $countRw,
            ];
        }

        if ($roleName === 'rt') {
            $rtId = $user->rt_id;
            $rt = $user->rt ?? ($rtId ? Rt::find($rtId) : null);
            $rw = $user->rw ?? ($rt?->rw ?? null);

            $rtNomor = $rt?->nomor_rt ?? '-';
            $rwNomor = $rw?->nomor_rw ?? '-';
            $totalWarga = $rtId ? Warga::where('rt_id', $rtId)->count() : 0;
            $lakiLaki = $rtId ? Warga::where('rt_id', $rtId)->where('jenis_kelamin', 'Laki-laki')->count() : 0;
            $perempuan = $rtId ? Warga::where('rt_id', $rtId)->where('jenis_kelamin', 'Perempuan')->count() : 0;
            $kkStats = $this->villageStatistikService->getKkStats(null, $rtId);
            $totalKk = $kkStats['total_kk'];

            return [
                'role' => 'rt',
                'has_data' => true,
                'rt_nomor' => $rtNomor,
                'rw_nomor' => $rwNomor,
                'ketua_rt' => $rt ? $this->resolveActiveOfficialName('rt', $rt->id, $user->name) : $user->name,
                'ketua_rw' => $rw ? $this->resolveActiveOfficialName('rw', $rw->id, $rw->ketua_rw) : '-',
                'total_warga' => $totalWarga,
                'laki_laki' => $lakiLaki,
                'perempuan' => $perempuan,
                'total_kk' => $totalKk,
            ];
        }

        if ($roleName === 'rw') {
            $rwId = $user->rw_id;
            $rw = $user->rw ?? ($rwId ? Rw::find($rwId) : null);
            $rwNomor = $rw?->nomor_rw ?? '-';

            $totalWarga = $rwId ? Warga::where('rw_id', $rwId)->count() : 0;
            $lakiLaki = $rwId ? Warga::where('rw_id', $rwId)->where('jenis_kelamin', 'Laki-laki')->count() : 0;
            $perempuan = $rwId ? Warga::where('rw_id', $rwId)->where('jenis_kelamin', 'Perempuan')->count() : 0;
            $kkStats = $this->villageStatistikService->getKkStats($rwId, null);
            $totalKk = $kkStats['total_kk'];

            $perRt = [];
            if ($rwId) {
                $rts = Rt::where('rw_id', $rwId)->orderBy('nomor_rt')->get();
                foreach ($rts as $rtItem) {
                    $perRt[] = [
                        'nomor_rt' => $rtItem->nomor_rt,
                        'total_warga' => Warga::where('rt_id', $rtItem->id)->count(),
                        'ketua_rt' => $this->resolveActiveOfficialName('rt', $rtItem->id, $rtItem->ketua_rt),
                    ];
                }
            }

            return [
                'role' => 'rw',
                'has_data' => true,
                'rw_nomor' => $rwNomor,
                'ketua_rw' => $rw ? $this->resolveActiveOfficialName('rw', $rw->id, $user->name) : $user->name,
                'total_warga' => $totalWarga,
                'laki_laki' => $lakiLaki,
                'perempuan' => $perempuan,
                'total_kk' => $totalKk,
                'per_rt' => $perRt,
            ];
        }

        // Super Admin / default
        $totalWarga = Warga::count();
        $lakiLaki = Warga::where('jenis_kelamin', 'Laki-laki')->count();
        $perempuan = Warga::where('jenis_kelamin', 'Perempuan')->count();
        $kkStats = $this->villageStatistikService->getKkStats();
        $totalKk = $kkStats['total_kk'];
        $totalRt = Rt::count();
        $totalRw = Rw::count();

        // Rincian per RW untuk Super Admin
        $perRw = [];
        $rws = Rw::with('rts')->orderBy('nomor_rw')->get();
        foreach ($rws as $rwItem) {
            $perRw[] = [
                'nomor_rw' => $rwItem->nomor_rw,
                'ketua_rw' => $this->resolveActiveOfficialName('rw', $rwItem->id, $rwItem->ketua_rw),
                'total_warga' => Warga::where('rw_id', $rwItem->id)->count(),
                'total_rt' => $rwItem->rts->count(),
            ];
        }

        return [
            'role' => 'super_admin',
            'has_data' => true,
            'total_warga' => $totalWarga,
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
            'total_kk' => $totalKk,
            'total_rt' => $totalRt,
            'total_rw' => $totalRw,
            'per_rw' => $perRw,
        ];
    }

    /**
     * Mengambil daftar nama warga terotorisasi backend (hanya jika diizinkan oleh hak akses/role dan dibatasi cakupan wilayahnya).
     *
     * Warga biasa: TIDAK DIIZINKAN melihat daftar nama warga lain (privasi kependudukan).
     * RT: Hanya melihat daftar nama warga di wilayah RT-nya.
     * RW: Hanya melihat daftar nama warga di wilayah RW-nya.
     * Super Admin: Melihat daftar nama warga seluruh desa (dibatasi pagination/limit representatif agar token aman).
     */
    public function getDaftarWargaData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'daftar_warga') {
            return null;
        }

        if ($roleName === 'warga') {
            return [
                'allowed' => false,
                'role' => 'warga',
                'reason' => 'Demi menjaga privasi kependudukan, daftar nama warga tidak dapat diakses oleh akun warga.',
                'items' => [],
            ];
        }

        $query = Warga::query()->select(['id', 'nama_lengkap', 'jenis_kelamin', 'rw_id', 'rt_id']);

        if ($roleName === 'rt') {
            if (! $user->rt_id) {
                return [
                    'allowed' => true,
                    'role' => 'rt',
                    'has_data' => false,
                    'message' => 'Akun pengurus RT belum terhubung dengan RT spesifik.',
                    'items' => [],
                ];
            }
            $query->where('rt_id', $user->rt_id);
        } elseif ($roleName === 'rw') {
            if (! $user->rw_id) {
                return [
                    'allowed' => true,
                    'role' => 'rw',
                    'has_data' => false,
                    'message' => 'Akun pengurus RW belum terhubung dengan RW spesifik.',
                    'items' => [],
                ];
            }
            $query->where('rw_id', $user->rw_id);
        }

        $totalCount = (clone $query)->count();
        $items = $query->with(['rt', 'rw'])->orderBy('nama_lengkap')->take(25)->get()->map(function ($w) {
            return [
                'nama' => $w->nama_lengkap,
                'jenis_kelamin' => $w->jenis_kelamin,
                'rt' => $w->rt?->nomor_rt ?? '-',
                'rw' => $w->rw?->nomor_rw ?? '-',
            ];
        })->toArray();

        return [
            'allowed' => true,
            'role' => $roleName,
            'has_data' => $totalCount > 0,
            'total' => $totalCount,
            'displayed_count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Mengambil data pengurus / ketua RT / ketua RW / Kepala Desa secara terotorisasi.
     * Mengutamakan akun pengurus/pejabat aktif dari tabel users, bukan teks statis tabel rw/rt.
     */
    public function getKetuaWilayahData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'ketua_wilayah' && $intent !== 'info_wilayah') {
            return null;
        }

        $desa = Desa::first();
        $kepalaDesa = $desa?->kepala_desa ?? 'Belum terdata';

        if ($roleName === 'warga') {
            $warga = $user->warga;
            $rt = $warga?->rt ?? ($user->rt_id ? Rt::find($user->rt_id) : null);
            $rw = $warga?->rw ?? ($user->rw_id ? Rw::find($user->rw_id) : null);

            $ketuaRt = $rt ? $this->resolveActiveOfficialName('rt', $rt->id, $rt->ketua_rt) : 'Belum terdata';
            $kontakRt = $rt ? $this->resolveActiveOfficialContact('rt', $rt->id, $rt->kontak) : '-';
            $ketuaRw = $rw ? $this->resolveActiveOfficialName('rw', $rw->id, $rw->ketua_rw) : 'Belum terdata';
            $kontakRw = $rw ? $this->resolveActiveOfficialContact('rw', $rw->id, $rw->kontak) : '-';

            return [
                'role' => 'warga',
                'kepala_desa' => $kepalaDesa,
                'rt_nomor' => $rt?->nomor_rt ?? '-',
                'ketua_rt' => $ketuaRt,
                'kontak_rt' => $kontakRt,
                'rw_nomor' => $rw?->nomor_rw ?? '-',
                'ketua_rw' => $ketuaRw,
                'kontak_rw' => $kontakRw,
            ];
        }

        if ($roleName === 'rt') {
            $rt = $user->rt ?? ($user->rt_id ? Rt::find($user->rt_id) : null);
            $rw = $user->rw ?? ($rt?->rw ?? null);

            $ketuaRt = $rt ? $this->resolveActiveOfficialName('rt', $rt->id, $user->name) : $user->name;
            $kontakRt = $rt ? $this->resolveActiveOfficialContact('rt', $rt->id, $user->nomor_telepon) : ($user->nomor_telepon ?? '-');
            $ketuaRw = $rw ? $this->resolveActiveOfficialName('rw', $rw->id, $rw->ketua_rw) : '-';
            $kontakRw = $rw ? $this->resolveActiveOfficialContact('rw', $rw->id, $rw->kontak) : '-';

            return [
                'role' => 'rt',
                'kepala_desa' => $kepalaDesa,
                'rt_nomor' => $rt?->nomor_rt ?? '-',
                'ketua_rt' => $ketuaRt,
                'kontak_rt' => $kontakRt,
                'rw_nomor' => $rw?->nomor_rw ?? '-',
                'ketua_rw' => $ketuaRw,
                'kontak_rw' => $kontakRw,
            ];
        }

        if ($roleName === 'rw') {
            $rw = $user->rw ?? ($user->rw_id ? Rw::find($user->rw_id) : null);
            $rts = $rw ? Rt::where('rw_id', $rw->id)->get() : collect();

            $ketuaRw = $rw ? $this->resolveActiveOfficialName('rw', $rw->id, $user->name) : $user->name;

            $listRt = $rts->map(function ($r) {
                return [
                    'nomor_rt' => $r->nomor_rt,
                    'ketua_rt' => $this->resolveActiveOfficialName('rt', $r->id, $r->ketua_rt),
                    'kontak' => $this->resolveActiveOfficialContact('rt', $r->id, $r->kontak),
                ];
            })->toArray();

            return [
                'role' => 'rw',
                'kepala_desa' => $kepalaDesa,
                'rw_nomor' => $rw?->nomor_rw ?? '-',
                'ketua_rw' => $ketuaRw,
                'rts' => $listRt,
            ];
        }

        // Super Admin
        $allRw = Rw::with('rts')->orderBy('nomor_rw')->get()->map(function ($rwItem) {
            return [
                'nomor_rw' => $rwItem->nomor_rw,
                'ketua_rw' => $this->resolveActiveOfficialName('rw', $rwItem->id, $rwItem->ketua_rw),
                'kontak' => $this->resolveActiveOfficialContact('rw', $rwItem->id, $rwItem->kontak),
                'rts' => $rwItem->rts->map(function ($rtItem) {
                    return [
                        'nomor_rt' => $rtItem->nomor_rt,
                        'ketua_rt' => $this->resolveActiveOfficialName('rt', $rtItem->id, $rtItem->ketua_rt),
                        'kontak' => $this->resolveActiveOfficialContact('rt', $rtItem->id, $rtItem->kontak),
                    ];
                })->toArray(),
            ];
        })->toArray();

        return [
            'role' => 'super_admin',
            'kepala_desa' => $kepalaDesa,
            'rws' => $allRw,
        ];
    }

    /**
     * Resolusi nama pejabat aktif dari tabel users berdasarkan role_id RW / RT.
     */
    public function resolveActiveOfficialName(string $roleType, int $wilayahId, ?string $fallback = null): string
    {
        $query = User::whereHas('role', fn ($q) => $q->where('name', $roleType))
            ->where('is_active', true);

        if ($roleType === 'rw') {
            $query->where('rw_id', $wilayahId);
        } else {
            $query->where('rt_id', $wilayahId);
        }

        $user = $query->latest('id')->first();

        if ($user && ! empty($user->name)) {
            return $user->name;
        }

        return (! empty($fallback) && $fallback !== '-') ? $fallback : 'Belum terdaftar';
    }

    /**
     * Resolusi kontak pejabat aktif dari tabel users atau tabel wilayah.
     */
    public function resolveActiveOfficialContact(string $roleType, int $wilayahId, ?string $fallback = null): string
    {
        $query = User::whereHas('role', fn ($q) => $q->where('name', $roleType))
            ->where('is_active', true);

        if ($roleType === 'rw') {
            $query->where('rw_id', $wilayahId);
        } else {
            $query->where('rt_id', $wilayahId);
        }

        $user = $query->latest('id')->first();

        if ($user && ! empty($user->nomor_telepon)) {
            return $user->nomor_telepon;
        }

        return (! empty($fallback) && $fallback !== '-') ? $fallback : '-';
    }

    /**
     * Mengambil Live Master Snapshot ringkasan seluruh desa (kependudukan, wilayah, akun per role, pengurus aktif).
     * Khusus disisipkan untuk Super Admin agar selalu memiliki data real-time akurat tanpa terhalang regex intent.
     */
    public function getLiveMasterSnapshot(): array
    {
        $penduduk = $this->villageStatistikService->getPendudukStats();
        $kk = $this->villageStatistikService->getKkStats();
        $wilayah = $this->villageStatistikService->getWilayahStats();
        $akun = $this->villageStatistikService->getAkunStats();
        $suratStats = $this->villageStatistikService->getPengajuanStats();

        $activeRwUsers = User::whereHas('role', fn ($q) => $q->where('name', 'rw'))
            ->where('is_active', true)
            ->with('rw')
            ->get()
            ->map(fn ($u) => [
                'name' => $u->name,
                'rw' => $u->rw?->nomor_rw ?? '-',
                'phone' => $u->nomor_telepon ?? '-',
                'email' => $u->email,
            ])
            ->toArray();

        $activeRtUsers = User::whereHas('role', fn ($q) => $q->where('name', 'rt'))
            ->where('is_active', true)
            ->with(['rt', 'rw'])
            ->get()
            ->map(fn ($u) => [
                'name' => $u->name,
                'rt' => $u->rt?->nomor_rt ?? '-',
                'rw' => $u->rw?->nomor_rw ?? ($u->rt?->rw?->nomor_rw ?? '-'),
                'phone' => $u->nomor_telepon ?? '-',
                'email' => $u->email,
            ])
            ->toArray();

        return [
            'total_warga' => $penduduk['total_warga'],
            'laki_laki' => $penduduk['laki_laki'],
            'perempuan' => $penduduk['perempuan'],
            'total_kk' => $kk['total_kk'],
            'total_rw' => $wilayah['total_rw'],
            'total_rt' => $wilayah['total_rt'],
            'total_users' => $akun['total'],
            'active_users' => $akun['active'],
            'users_per_role' => [
                'super_admin' => $akun['super_admin'],
                'admin_desa' => $akun['admin_desa'] ?? 0,
                'rw' => $akun['rw'],
                'rt' => $akun['rt'],
                'warga' => $akun['warga'],
            ],
            'active_rw_officials' => $activeRwUsers,
            'active_rt_officials' => $activeRtUsers,
            'total_surat' => $suratStats['total'],
            'surat_sedang_diproses' => $suratStats['menunggu_total'],
            'surat_selesai' => $suratStats['selesai'],
            'surat_ditolak' => $suratStats['ditolak'],
            'surat_menunggu_rt' => $suratStats['menunggu_rt'],
            'surat_menunggu_rw' => $suratStats['menunggu_rw'],
            'surat_diproses' => $suratStats['diproses'],
        ];
    }

    /**
     * Pencarian kata kunci dinamis (Dynamic Keyword Lookup) untuk mendeteksi nama orang atau entitas
     * spesifik yang disebutkan di pertanyaan pengguna, lalu mencari langsung ke tabel users / warga.
     */
    public function dynamicKeywordLookup(string $message, User $user, string $roleName): ?array
    {
        $clean = $this->normalizeQuery($message);

        $stopwords = [
            'siapa', 'nama', 'apakah', 'adakah', 'tolong', 'bisa', 'desa', 'pengurus',
            'ketua', 'warga', 'penduduk', 'bapak', 'ibu', 'pak', 'bu', 'mas', 'mbak',
            'berapa', 'jumlah', 'total', 'yang', 'dan', 'atau', 'dari', 'untuk', 'pada',
            'dengan', 'ada', 'itu', 'ini', 'adalah', 'saya', 'kami', 'anda', 'kamu',
            'surat', 'pengajuan', 'akun', 'user', 'role', 'admin', 'super', 'data',
            'terdaftar', 'status', 'aktif', 'tinggal', 'dimana', 'alamat', 'nomor',
            'halo', 'selamat', 'pagi', 'siang', 'sore', 'malam', 'terima', 'kasih',
        ];

        $words = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
        $candidateKeywords = [];

        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w) >= 3 && ! in_array($w, $stopwords, true) && ! is_numeric($w)) {
                $candidateKeywords[] = $w;
            }
        }

        if (empty($candidateKeywords)) {
            return null;
        }

        $results = [];

        // 1. Cari di tabel users
        $userQuery = User::with('role', 'rw', 'rt')->where('is_active', true);

        if ($roleName === 'rt' && $user->rt_id) {
            $userQuery->where(function ($q) use ($user) {
                $q->where('rt_id', $user->rt_id)
                    ->orWhereHas('role', fn ($sub) => $sub->whereIn('name', ['rw', 'super_admin']));
            });
        } elseif ($roleName === 'rw' && $user->rw_id) {
            $userQuery->where(function ($q) use ($user) {
                $q->where('rw_id', $user->rw_id)
                    ->orWhereHas('role', fn ($sub) => $sub->where('name', 'super_admin'));
            });
        } elseif ($roleName === 'warga') {
            $userQuery->whereHas('role', fn ($q) => $q->whereIn('name', ['rw', 'rt', 'super_admin']));
        }

        $userQuery->where(function ($q) use ($candidateKeywords) {
            foreach ($candidateKeywords as $kw) {
                $q->orWhere('name', 'like', "%{$kw}%")
                    ->orWhere('username', 'like', "%{$kw}%");
            }
        });

        $matchedUsers = $userQuery->take(5)->get();
        foreach ($matchedUsers as $mu) {
            $roleLabel = match ($mu->role?->name) {
                'super_admin' => 'Super Admin',
                'rw' => 'Ketua / Pengurus RW '.($mu->rw?->nomor_rw ?? '-'),
                'rt' => 'Ketua / Pengurus RT '.($mu->rt?->nomor_rt ?? '-').' RW '.($mu->rw?->nomor_rw ?? '-'),
                'warga' => 'Warga',
                default => 'User',
            };

            $results[] = [
                'tipe' => 'Akun Pengguna / Pejabat',
                'nama' => $mu->name,
                'jabatan_role' => $roleLabel,
                'status' => $mu->is_active ? 'Aktif' : 'Non-aktif',
                'wilayah' => 'RW '.($mu->rw?->nomor_rw ?? '-').' RT '.($mu->rt?->nomor_rt ?? '-'),
                'kontak' => $mu->nomor_telepon ?? '-',
            ];
        }

        // 2. Jika bukan warga biasa, cari juga di tabel warga
        if ($roleName !== 'warga') {
            $wargaQuery = Warga::with(['rt', 'rw'])->where(function ($q) {
                $q->where('status_aktif', 1)->orWhereNull('status_aktif');
            });

            if ($roleName === 'rt' && $user->rt_id) {
                $wargaQuery->where('rt_id', $user->rt_id);
            } elseif ($roleName === 'rw' && $user->rw_id) {
                $wargaQuery->where('rw_id', $user->rw_id);
            }

            $wargaQuery->where(function ($q) use ($candidateKeywords) {
                foreach ($candidateKeywords as $kw) {
                    $q->orWhere('nama_lengkap', 'like', "%{$kw}%");
                }
            });

            $matchedWarga = $wargaQuery->take(5)->get();
            foreach ($matchedWarga as $mw) {
                $results[] = [
                    'tipe' => 'Data Penduduk Warga',
                    'nama' => $mw->nama_lengkap,
                    'jabatan_role' => 'Warga (Status: '.$mw->status_dalam_keluarga.')',
                    'status' => 'Aktif',
                    'wilayah' => 'RT '.($mw->rt?->nomor_rt ?? '-').' RW '.($mw->rw?->nomor_rw ?? '-'),
                    'kontak' => $mw->nomor_telepon ?? '-',
                ];
            }
        }

        if (empty($results)) {
            return null;
        }

        return [
            'keywords' => $candidateKeywords,
            'total_found' => count($results),
            'items' => $results,
        ];
    }

    /**
     * Mengambil status pengajuan surat terotorisasi backend sesuai role user.
     * Warga hanya dapat melihat surat miliknya.
     * RT melihat surat di RT miliknya.
     * RW melihat surat di RW miliknya.
     * Super Admin melihat surat tingkat desa.
     */
    public function getPengajuanStatus(User $user, string $roleName, string $intent): ?array
    {
        // Hanya query jika intent berkaitan dengan status pengajuan, verifikasi, atau statistik surat
        if (! in_array($intent, ['status_pengajuan', 'verifikasi_surat', 'statistik_surat'], true)) {
            return null;
        }

        if ($roleName === 'warga') {
            $warga = $user->warga;
            if (! $warga) {
                return [
                    'role' => 'warga',
                    'has_data' => false,
                    'message' => 'Akun Anda belum terhubung dengan data warga, sehingga belum dapat memeriksa status pengajuan surat.',
                    'latest' => null,
                ];
            }

            $latest = PengajuanSurat::where('warga_id', $warga->id)
                ->with('template')
                ->latest('id')
                ->first();

            if (! $latest) {
                return [
                    'role' => 'warga',
                    'has_data' => false,
                    'message' => 'Anda belum memiliki riwayat pengajuan surat saat ini. Untuk membuat pengajuan baru, silakan buka menu Layanan Surat.',
                    'latest' => null,
                ];
            }

            return [
                'role' => 'warga',
                'has_data' => true,
                'latest' => [
                    'id' => $latest->id,
                    'nomor_surat' => $latest->nomor_surat,
                    'nama_surat' => $latest->template?->nama_surat ?? 'Surat',
                    'status' => $latest->status,
                    'catatan' => $latest->catatan,
                    'approved_at' => $latest->approved_at?->format('d/m/Y H:i'),
                    'file_pdf' => $latest->file_pdf,
                    'created_at' => $latest->created_at?->format('d/m/Y H:i'),
                ],
            ];
        }

        // Pengurus RT / RW / Super Admin: ambil rekap ringkas status pengajuan di wilayahnya
        $query = PengajuanSurat::with(['warga', 'template']);

        if ($roleName === 'rt') {
            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('rt_id', $user->rt_id);
            });
        } elseif ($roleName === 'rw') {
            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('rw_id', $user->rw_id);
            });
        }

        $total = (clone $query)->count();
        $menunggu = (clone $query)->whereIn('status', ['MENUNGGU', 'MENUNGGU_RT', 'MENUNGGU_RW', 'DIPROSES'])->count();
        $selesai = (clone $query)->where('status', 'SELESAI')->count();
        $ditolak = (clone $query)->where('status', 'DITOLAK')->count();

        $menungguRt = (clone $query)->where('status', 'MENUNGGU_RT')->count();
        $menungguRw = (clone $query)->where('status', 'MENUNGGU_RW')->count();
        $butuhTindakan = match ($roleName) {
            'rt' => $menungguRt,
            'rw' => $menungguRw,
            default => $menunggu,
        };

        $recentList = $query->latest('id')->take(3)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nama_warga' => $p->warga?->nama_lengkap ?? 'Warga',
                'nama_surat' => $p->template?->nama_surat ?? 'Surat',
                'status' => $p->status,
            ];
        })->toArray();

        return [
            'role' => $roleName,
            'has_data' => $total > 0,
            'summary' => [
                'total' => $total,
                'menunggu' => $menunggu,
                'selesai' => $selesai,
                'ditolak' => $ditolak,
                'butuh_tindakan' => $butuhTindakan,
                'menunggu_rt' => $menungguRt,
                'menunggu_rw' => $menungguRw,
            ],
            'recent' => $recentList,
        ];
    }

    /**
     * Mengambil statistik pengajuan surat per status secara terotorisasi dari database live.
     * Status aktual di database: DRAFT | MENUNGGU_RT | MENUNGGU_RW | DIPROSES | SELESAI | DITOLAK
     */
    public function getSuratStatistikData(User $user, string $roleName, ?string $intent = null): ?array
    {
        if ($intent !== null && ! in_array($intent, ['statistik_surat', 'status_pengajuan', 'verifikasi_surat', 'layanan_surat', 'permohonan_surat'], true)) {
            return null;
        }

        $query = PengajuanSurat::query();

        if ($roleName === 'warga') {
            $warga = $user->warga;
            if (! $warga) {
                return [
                    'role' => 'warga',
                    'has_data' => false,
                    'message' => 'Akun Anda belum terhubung dengan data warga.',
                ];
            }

            $query->where('warga_id', $warga->id);
        } elseif ($roleName === 'rt') {
            if (! $user->rt_id) {
                return [
                    'role' => 'rt',
                    'has_data' => false,
                    'message' => 'Akun pengurus RT belum terhubung ke wilayah RT spesifik.',
                ];
            }

            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('rt_id', $user->rt_id);
            });
        } elseif ($roleName === 'rw') {
            if (! $user->rw_id) {
                return [
                    'role' => 'rw',
                    'has_data' => false,
                    'message' => 'Akun pengurus RW belum terhubung ke wilayah RW spesifik.',
                ];
            }

            $query->whereHas('warga', function ($q) use ($user) {
                $q->where('rw_id', $user->rw_id);
            });
        }
        // super_admin: tidak ada filter wilayah, melihat rekap seluruh desa

        $total = (clone $query)->count();
        $selesai = (clone $query)->where('status', 'SELESAI')->count();
        $menungguRt = (clone $query)->where('status', 'MENUNGGU_RT')->count();
        $menungguRw = (clone $query)->where('status', 'MENUNGGU_RW')->count();
        $diproses = (clone $query)->where('status', 'DIPROSES')->count();
        $draft = (clone $query)->where('status', 'DRAFT')->count();
        $ditolak = (clone $query)->where('status', 'DITOLAK')->count();
        $sedangDiproses = $menungguRt + $menungguRw + $diproses + $draft;

        return [
            'role' => $roleName,
            'has_data' => true,
            'total' => $total,
            'selesai' => $selesai,
            'disetujui' => $selesai,
            'sedang_diproses' => $sedangDiproses,
            'menunggu' => $sedangDiproses,
            'pending' => $sedangDiproses,
            'menunggu_rt' => $menungguRt,
            'menunggu_rw' => $menungguRw,
            'diproses' => $diproses,
            'draft' => $draft,
            'ditolak' => $ditolak,
        ];
    }

    /**
     * Mengambil informasi publik resmi terbaru dari tabel informasi.
     */
    public function getInformasiTerbaru(User $user, string $roleName, string $intent): array
    {
        if ($intent !== 'info_publik' && $intent !== 'general') {
            return [];
        }

        $query = Informasi::whereIn('status_publikasi', [1, '1', true, 'published']);

        // Filter scope wilayah jika ada
        if ($roleName === 'rt' && $user->rt_id) {
            $query->where(function ($q) use ($user) {
                $q->where('scope_wilayah', 'desa')
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('scope_wilayah', 'rt')->where('rt_id', $user->rt_id);
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('scope_wilayah', 'rw')->where('rw_id', $user->rw_id);
                    });
            });
        } elseif ($roleName === 'rw' && $user->rw_id) {
            $query->where(function ($q) use ($user) {
                $q->where('scope_wilayah', 'desa')
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('scope_wilayah', 'rw')->where('rw_id', $user->rw_id);
                    });
            });
        }

        return $query->latest('tanggal')
            ->take(3)
            ->get(['id', 'judul', 'kategori', 'tanggal'])
            ->toArray();
    }

    /**
     * Mengambil ringkasan data akun pengguna sistem secara terotorisasi.
     */
    public function getAkunPenggunaData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'data_akun') {
            return null;
        }

        if ($roleName === 'warga') {
            return [
                'role' => 'warga',
                'has_account' => true,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'status_aktif' => $user->is_active ? 'Aktif' : 'Non-aktif',
            ];
        }

        $query = User::query()->with('role');

        if ($roleName === 'rt' && $user->rt_id) {
            $query->where('rt_id', $user->rt_id);
        } elseif ($roleName === 'rw' && $user->rw_id) {
            $query->where('rw_id', $user->rw_id);
        }

        $totalUsers = (clone $query)->count();
        $activeUsers = (clone $query)->where('is_active', true)->count();

        $roleCounts = [];
        $usersList = (clone $query)->get();
        foreach ($usersList as $u) {
            $rName = $u->role ? $u->role->name : 'unassigned';
            $roleCounts[$rName] = ($roleCounts[$rName] ?? 0) + 1;
        }

        return [
            'role' => $roleName,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'per_role' => $roleCounts,
        ];
    }

    /**
     * Mengambil data statistik wilayah (jumlah RW dan RT unik) secara terotorisasi.
     * Mengakses tabel 'rw' dan 'rt' secara langsung.
     */
    public function getWilayahStatistikData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'statistik_wilayah' && $intent !== 'info_wilayah') {
            return null;
        }

        $stats = $this->villageStatistikService->getWilayahStats($user->rw_id);

        $data = [
            'role' => $roleName,
            'has_data' => true,
            'total_rw' => $stats['total_rw'],
            'total_rt' => $stats['total_rt'],
            'rws' => $stats['rws'],
        ];

        if ($roleName === 'rw') {
            $rw = $user->rw;
            $data['rw_id'] = $user->rw_id;
            $data['rw_nomor'] = $rw?->nomor_rw ?? '-';
            $data['total_rt_binaan'] = $stats['rt_binaan'];
        } elseif ($roleName === 'rt') {
            $rt = $user->rt;
            $rw = $user->rw ?? $rt?->rw;
            $data['rt_id'] = $user->rt_id;
            $data['rt_nomor'] = $rt?->nomor_rt ?? '-';
            $data['rw_nomor'] = $rw?->nomor_rw ?? '-';
        } elseif ($roleName === 'warga') {
            $warga = $user->warga;
            $data['rt_nomor'] = $warga?->rt?->nomor_rt ?? ($user->rt?->nomor_rt ?? '-');
            $data['rw_nomor'] = $warga?->rw?->nomor_rw ?? ($user->rw?->nomor_rw ?? '-');
        }

        return $data;
    }

    /**
     * Mengambil statistik akun pengguna berdasarkan role yang sebenarnya.
     * Memisahkan data akun role dari entitas fisik wilayah RW/RT.
     */
    public function getAkunStatistikData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'statistik_akun' && $intent !== 'data_akun') {
            return null;
        }

        if ($roleName === 'warga') {
            return [
                'role' => 'warga',
                'has_data' => true,
                'is_personal' => true,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'status_aktif' => $user->is_active ? 'Aktif' : 'Non-aktif',
            ];
        }

        $rwId = $roleName === 'rw' ? $user->rw_id : null;
        $rtId = $roleName === 'rt' ? $user->rt_id : null;
        $akun = $this->villageStatistikService->getAkunStats($rwId, $rtId);
        $wilayah = $this->villageStatistikService->getWilayahStats();

        return [
            'role' => $roleName,
            'has_data' => true,
            'is_personal' => false,
            'total' => $akun['total'],
            'active' => $akun['active'],
            'super_admin' => $akun['super_admin'],
            'rw' => $akun['rw'],
            'rt' => $akun['rt'],
            'warga' => $akun['warga'],
            'active_rw' => $akun['active_rw'],
            'active_rt' => $akun['active_rt'],
            'active_warga' => $akun['active_warga'],
            'active_super_admin' => $akun['active_super_admin'],
            'per_role' => $akun['per_role'],
            'wilayah_rw' => $wilayah['total_rw'],
            'wilayah_rt' => $wilayah['total_rt'],
        ];
    }

    /**
     * Mengambil statistik Kartu Keluarga (KK) dan Kepala Keluarga.
     * Menggunakan VillageStatistikService untuk hasil yang sinkron dengan dashboard.
     */
    public function getKepalaKeluargaStatistikData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'statistik_kepala_keluarga' && $intent !== 'jumlah_warga' && $intent !== 'statistik_penduduk') {
            return null;
        }

        $rwId = $roleName === 'rw' ? $user->rw_id : null;
        $rtId = $roleName === 'rt' ? $user->rt_id : null;

        $stats = $this->villageStatistikService->getKkStats($rwId, $rtId);

        $wargaKkQuery = Warga::query()->where('status_dalam_keluarga', 'Kepala Keluarga');
        if ($rtId) {
            $wargaKkQuery->where('rt_id', $rtId);
        } elseif ($rwId) {
            $wargaKkQuery->where('rw_id', $rwId);
        }
        $kepalaKeluargaFirst = $wargaKkQuery->first();

        return [
            'role' => $roleName,
            'has_data' => true,
            'total_kk' => $stats['total_kk'],
            'total_kk_terdaftar' => $stats['total_kk_terdaftar'],
            'total_kepala_keluarga_warga' => $stats['total_kepala_keluarga'],
            'total_nomor_kk_unik' => count($stats['unique_nomor_kk']),
            'nama_kepala_keluarga' => $kepalaKeluargaFirst?->nama_lengkap,
            'nomor_kk_contoh' => $kepalaKeluargaFirst?->nomor_kk,
        ];
    }

    /**
     * Mengambil data jenis layanan surat dan template aktif.
     */
    public function getLayananSuratData(string $intent): ?array
    {
        if ($intent !== 'layanan_surat' && $intent !== 'daftar_surat') {
            return null;
        }

        $templates = SuratTemplate::where('is_active', true)->get(['id', 'nama_surat', 'kode_surat', 'deskripsi']);

        return [
            'has_data' => $templates->isNotEmpty(),
            'total' => $templates->count(),
            'list' => $templates->map(function ($t) {
                return $t->nama_surat.($t->kode_surat ? " ({$t->kode_surat})" : '');
            })->toArray(),
        ];
    }

    /**
     * Mengambil data notifikasi in-app surat terotorisasi.
     */
    public function getNotifikasiData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'notifikasi') {
            return null;
        }

        $stats = $this->villageStatistikService->getNotifikasiStats($user->id, $user->warga?->id);

        $query = SuratNotification::query();
        if ($roleName === 'warga') {
            $wId = $user->warga?->id;
            $uId = $user->id;
            $query->where(function ($q) use ($wId, $uId) {
                if ($wId) {
                    $q->where('recipient_warga_id', $wId);
                }
                if ($uId) {
                    $q->orWhere('recipient_user_id', $uId);
                }
            });
        }

        $recent = $query->latest('id')->take(3)->get()->map(function ($n) {
            return [
                'event_status' => $n->event_status,
                'status_kirim' => $n->status,
                'sent_at' => $n->sent_at?->format('d/m/Y H:i') ?? $n->created_at?->format('d/m/Y H:i'),
                'pesan_ringkas' => mb_substr(strip_tags((string) $n->message), 0, 100),
            ];
        })->toArray();

        return [
            'role' => $roleName,
            'has_data' => $stats['total'] > 0,
            'total' => $stats['total'],
            'sent' => $stats['sent'],
            'failed' => $stats['failed'],
            'pending' => $stats['pending'],
            'recent' => $recent,
        ];
    }

    /**
     * Mengambil data riwayat pengiriman WhatsApp (Fonnte) aktual dari tabel log.
     * Tidak menganggap pembuatan record sebagai jaminan pengiriman; status riil dibaca dari log.
     */
    public function getWhatsappLogData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'whatsapp_log') {
            return null;
        }

        $stats = $this->villageStatistikService->getWhatsappStats($user->id, $user->warga?->id);

        $broadcasts = WhatsappBroadcast::with('informasi')->latest('id')->take(3)->get()->map(function ($b) {
            return [
                'id' => $b->id,
                'judul_informasi' => $b->informasi?->judul ?? 'Pengumuman',
                'target' => $b->target_description,
                'total' => $b->total_recipients,
                'success' => $b->success_count,
                'failed' => $b->failed_count,
                'status' => $b->status,
                'tanggal' => $b->created_at?->format('d/m/Y H:i'),
            ];
        })->toArray();

        return [
            'role' => $roleName,
            'has_data' => $stats['total_broadcasts'] > 0 || $stats['total_recipients'] > 0,
            'total_broadcasts' => $stats['total_broadcasts'],
            'total_recipients' => $stats['total_recipients'],
            'berhasil' => $stats['berhasil'],
            'gagal' => $stats['gagal'],
            'menunggu' => $stats['menunggu'],
            'recent_broadcasts' => $broadcasts,
        ];
    }

    /**
     * Mengambil log aktivitas (Audit Log) terotorisasi.
     */
    public function getAktivitasLogData(User $user, string $roleName, string $intent): ?array
    {
        if ($intent !== 'aktivitas_log') {
            return null;
        }

        $userId = $roleName === 'warga' ? $user->id : null;
        $stats = $this->villageStatistikService->getAktivitasStats($userId);

        $query = AuditLog::query();
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $recent = $query->latest('id')->take(3)->get()->map(function ($l) {
            return [
                'activity' => $l->activity,
                'model' => class_basename((string) $l->model_type),
                'waktu' => $l->created_at?->format('d/m/Y H:i'),
            ];
        })->toArray();

        return [
            'role' => $roleName,
            'has_data' => $stats['total'] > 0,
            'total' => $stats['total'],
            'recent' => $recent,
        ];
    }

    /**
     * Menghasilkan peta fitur aktif PrajaDigital beserta batas kemampuan chatbot.
     */
    public function getFeatureGuideData(User $user, string $roleName, string $intent): array
    {
        return [
            'features' => [
                'dashboard' => [
                    'nama' => 'Dashboard Eksekutif & Warga',
                    'deskripsi' => 'Ringkasan metrik statistik kependudukan, KK, layanan surat, dan informasi terkini.',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Data',
                ],
                'penduduk' => [
                    'nama' => 'Kependudukan & Warga',
                    'deskripsi' => 'Pengelolaan data penduduk desa (NIK, status keluarga, alamat, wilayah RT/RW).',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Data (Terotorisasi)',
                ],
                'kk' => [
                    'nama' => 'Kartu Keluarga (KK)',
                    'deskripsi' => 'Pencatatan berkas KK dan data Kepala Keluarga.',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Data',
                ],
                'wilayah' => [
                    'nama' => 'Wilayah Administratif (RW & RT)',
                    'deskripsi' => 'Entitas pembagian wilayah administratif RW dan RT desa beserta kontak ketua wilayah.',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Data',
                ],
                'surat' => [
                    'nama' => 'Layanan & Pengajuan Surat',
                    'deskripsi' => 'Pengajuan surat online berjenjang (Warga -> RT -> RW -> Selesai) dan unduh PDF.',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Data Status/Rekap',
                ],
                'pengumuman' => [
                    'nama' => 'Informasi & Pengumuman Desa',
                    'deskripsi' => 'Publikasi kabar desa, kegiatan, dan broadcast pengumuman via WhatsApp (Fonnte).',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Data',
                ],
                'whatsapp' => [
                    'nama' => 'Integrasi WhatsApp Fonnte',
                    'deskripsi' => 'Pengiriman notifikasi otomatis dan siaran informasi via gateway WhatsApp resmi.',
                    'kemampuan_chatbot' => 'Jelaskan & Baca Log Pengiriman',
                ],
                'konfigurasi' => [
                    'nama' => 'Konfigurasi Sistem & AI',
                    'deskripsi' => 'Status koneksi AI Gemini/Mock, gateway WhatsApp, dan info sistem terenkripsi/masked.',
                    'kemampuan_chatbot' => 'Jelaskan Status Sistem',
                ],
            ],
        ];
    }

    /**
     * Format ringkasan data database untuk disematkan ke dalam prompt AI sistem.
     * Mengirimkan hanya data ringkas dan terotorisasi.
     */
    public function formatContextForSystemPrompt(array $context): string
    {
        $lines = [];

        // 1. Profil Pengguna
        $profile = $context['user_profile'];
        $lines[] = 'PROFIL DAN OTORISASI PENGGUNA:';
        $lines[] = "- Nama: {$profile['name']}";
        $lines[] = "- Role: {$profile['role']}";
        $lines[] = "- Wilayah: {$profile['scope_description']}";

        // 1b. Live Master Snapshot (Khusus Super Admin - selalu disematkan tanpa terhalang intent)
        if (! empty($context['live_master_snapshot'])) {
            $lms = $context['live_master_snapshot'];
            $lines[] = "\n========================================================";
            $lines[] = 'LIVE MASTER SNAPSHOT DESA (DATA REAL-TIME DATABASE):';
            $lines[] = '========================================================';
            $lines[] = "- Total Penduduk Warga: {$lms['total_warga']} jiwa ({$lms['laki_laki']} laki-laki, {$lms['perempuan']} perempuan)";
            $lines[] = "- Total Kartu Keluarga (KK): {$lms['total_kk']} KK";
            $lines[] = "- Wilayah Administratif Desa: {$lms['total_rw']} RW dan {$lms['total_rt']} RT";
            $lines[] = "- Total Akun Pengguna Terdaftar: {$lms['total_users']} akun ({$lms['active_users']} aktif)";
            $lines[] = "  * Akun Super Admin: {$lms['users_per_role']['super_admin']}";
            if (isset($lms['users_per_role']['admin_desa'])) {
                $lines[] = "  * Akun Admin Desa: {$lms['users_per_role']['admin_desa']}";
            }
            $lines[] = "  * Akun Pengurus RW: {$lms['users_per_role']['rw']}";
            $lines[] = "  * Akun Pengurus RT: {$lms['users_per_role']['rt']}";
            $lines[] = "  * Akun Warga: {$lms['users_per_role']['warga']}";
            if (isset($lms['total_surat'])) {
                $lines[] = "- Layanan Surat (Real-Time Rekap): Total {$lms['total_surat']} berkas permohonan";
                $lines[] = "  * Sedang Diproses / Pending: {$lms['surat_sedang_diproses']} berkas (Menunggu RT: {$lms['surat_menunggu_rt']}, Menunggu RW: {$lms['surat_menunggu_rw']}, Diproses Petugas: {$lms['surat_diproses']})";
                $lines[] = "  * Selesai / Disetujui: {$lms['surat_selesai']} berkas";
                $lines[] = "  * Ditolak: {$lms['surat_ditolak']} berkas";
            }
            if (! empty($lms['active_rw_officials'])) {
                $lines[] = '- Pengurus RW Aktif Terdaftar:';
                foreach ($lms['active_rw_officials'] as $rwUser) {
                    $lines[] = "  * RW {$rwUser['rw']}: {$rwUser['name']} (Kontak: {$rwUser['phone']})";
                }
            }
            if (! empty($lms['active_rt_officials'])) {
                $lines[] = '- Pengurus RT Aktif Terdaftar:';
                foreach ($lms['active_rt_officials'] as $rtUser) {
                    $lines[] = "  * RT {$rtUser['rt']} RW {$rtUser['rw']}: {$rtUser['name']} (Kontak: {$rtUser['phone']})";
                }
            }
            $lines[] = "========================================================\n";
        }

        // 1c. Hasil Pencarian Kata Kunci / Nama Dinamis (Dynamic Keyword Lookup)
        if (! empty($context['keyword_lookup'])) {
            $kl = $context['keyword_lookup'];
            $kwStr = implode(', ', $kl['keywords']);
            $lines[] = "\nHASIL PENCARIAN REAL-TIME KATA KUNCI/NAMA ('{$kwStr}') DARI DATABASE:";
            foreach ($kl['items'] as $item) {
                $lines[] = "- [{$item['tipe']}] {$item['nama']} | Peran: {$item['jabatan_role']} | Wilayah: {$item['wilayah']} | Status: {$item['status']} | Kontak: {$item['kontak']}";
            }
            $lines[] = 'INSTRUKSI: Gunakan data pencarian di atas untuk menjawab jika pengguna menanyakan nama, status, atau jabatan orang tersebut.';
        }

        // 2. Info Desa
        if (! empty($context['desa_info'])) {
            $d = $context['desa_info'];
            $lines[] = "\nINFORMASI RESMI DESA:";
            $lines[] = "- Nama Desa: {$d['nama']}";
            $lines[] = "- Kecamatan: {$d['kecamatan']}, Kabupaten/Kota: {$d['kabupaten_kota']}, Provinsi: {$d['provinsi']}";
            $lines[] = "- Alamat: {$d['alamat']}".($d['kode_pos'] ? " (Kode Pos: {$d['kode_pos']})" : '');
            $lines[] = '- Kepala Desa: '.($d['kepala_desa'] ?? 'Belum terdata');
            $lines[] = '- Kontak/Layanan: '.($d['kontak'] ?? 'Belum terdata');
        }

        // 3. Data Kependudukan & Statistik
        if (! empty($context['kependudukan']) && ($context['kependudukan']['has_data'] ?? false)) {
            $k = $context['kependudukan'];
            $lines[] = "\nDATA KEPENDUDUKAN & STATISTIK RESMI DARI DATABASE:";
            if ($k['role'] === 'warga') {
                $lines[] = "- Wilayah Domisili Pengguna: RT {$k['rt_nomor']} RW {$k['rw_nomor']}";
                $lines[] = "- Jumlah Warga di RT {$k['rt_nomor']}: {$k['jumlah_warga_rt']} orang";
                $lines[] = "- Jumlah Warga di RW {$k['rw_nomor']}: {$k['jumlah_warga_rw']} orang";
                if (! empty($k['ketua_rt']) && $k['ketua_rt'] !== '-') {
                    $lines[] = "- Ketua RT {$k['rt_nomor']}: {$k['ketua_rt']}";
                }
                if (! empty($k['ketua_rw']) && $k['ketua_rw'] !== '-') {
                    $lines[] = "- Ketua RW {$k['rw_nomor']}: {$k['ketua_rw']}";
                }
                $lines[] = '- BATASAN PRIVASI: Pengguna adalah Warga. Warga HANYA boleh mengetahui agregat jumlah warga di RT/RW-nya sendiri, dan DILARANG diberikan data pribadi (nama, NIK, alamat spesifik, kontak) warga lain.';
            } elseif ($k['role'] === 'rt') {
                $lines[] = "- Wilayah Kewenangan: RT {$k['rt_nomor']} RW {$k['rw_nomor']}";
                $lines[] = "- Total Warga di RT {$k['rt_nomor']}: {$k['total_warga']} orang ({$k['laki_laki']} Laki-laki, {$k['perempuan']} Perempuan)";
                $lines[] = "- Total Kepala Keluarga (KK): {$k['total_kk']} KK";
            } elseif ($k['role'] === 'rw') {
                $lines[] = "- Wilayah Kewenangan: RW {$k['rw_nomor']}";
                $lines[] = "- Total Warga di RW {$k['rw_nomor']}: {$k['total_warga']} orang ({$k['laki_laki']} Laki-laki, {$k['perempuan']} Perempuan)";
                $lines[] = "- Total Kepala Keluarga (KK): {$k['total_kk']} KK";
                if (! empty($k['per_rt'])) {
                    $lines[] = '- Rincian Jumlah Warga per RT:';
                    foreach ($k['per_rt'] as $rtItem) {
                        $lines[] = "  * RT {$rtItem['nomor_rt']}: {$rtItem['total_warga']} orang warga (Ketua RT: {$rtItem['ketua_rt']})";
                    }
                }
            } elseif ($k['role'] === 'super_admin') {
                $lines[] = '- Cakupan: Seluruh Desa';
                $lines[] = "- Total Warga Desa: {$k['total_warga']} orang ({$k['laki_laki']} Laki-laki, {$k['perempuan']} Perempuan)";
                $lines[] = "- Total Kepala Keluarga (KK): {$k['total_kk']} KK";
                $lines[] = "- Total Wilayah Administrasi: {$k['total_rw']} RW dan {$k['total_rt']} RT";
            }
        }

        // 4. Data Daftar Nama Warga Terotorisasi
        if (! empty($context['daftar_warga'])) {
            $dw = $context['daftar_warga'];
            $lines[] = "\nDATA DAFTAR WARGA TEROTORISASI:";
            if (! $dw['allowed']) {
                $lines[] = "- Akses Ditolak: {$dw['reason']}";
            } elseif (! ($dw['has_data'] ?? false)) {
                $lines[] = '- Data Warga: Saat ini belum ada data warga di wilayah Anda.';
            } else {
                $lines[] = "- Total Warga di Wilayah Kewenangan: {$dw['total']} orang (menampilkan {$dw['displayed_count']} data teratas):";
                foreach ($dw['items'] as $item) {
                    $lines[] = "  * {$item['nama']} ({$item['jenis_kelamin']}) - RT {$item['rt']} RW {$item['rw']}";
                }
            }
        }

        // 5. Data Ketua Wilayah (RT / RW / Kepala Desa)
        if (! empty($context['ketua_wilayah'])) {
            $kw = $context['ketua_wilayah'];
            $lines[] = "\nDATA KETUA WILAYAH RESMI:";
            $lines[] = "- Kepala Desa: {$kw['kepala_desa']}";
            if ($kw['role'] === 'warga' || $kw['role'] === 'rt') {
                $lines[] = "- Ketua RT {$kw['rt_nomor']}: {$kw['ketua_rt']} (Kontak: {$kw['kontak_rt']})";
                $lines[] = "- Ketua RW {$kw['rw_nomor']}: {$kw['ketua_rw']} (Kontak: {$kw['kontak_rw']})";
            } elseif ($kw['role'] === 'rw') {
                $lines[] = "- Ketua RW {$kw['rw_nomor']}: {$kw['ketua_rw']}";
                if (! empty($kw['rts'])) {
                    $lines[] = '- Daftar Ketua RT di bawah RW:';
                    foreach ($kw['rts'] as $rtItem) {
                        $lines[] = "  * RT {$rtItem['nomor_rt']}: {$rtItem['ketua_rt']} (Kontak: {$rtItem['kontak']})";
                    }
                }
            } elseif ($kw['role'] === 'super_admin' && ! empty($kw['rws'])) {
                $lines[] = '- Daftar Ketua RW & RT Seluruh Desa:';
                foreach ($kw['rws'] as $rwItem) {
                    $lines[] = "  * RW {$rwItem['nomor_rw']}: {$rwItem['ketua_rw']} (Kontak: {$rwItem['kontak']})";
                    foreach ($rwItem['rts'] as $rtItem) {
                        $lines[] = "    - RT {$rtItem['nomor_rt']}: {$rtItem['ketua_rt']} (Kontak: {$rtItem['kontak']})";
                    }
                }
            }
        }

        // 6. Template Layanan Surat Aktif
        if (! empty($context['surat_templates'])) {
            $lines[] = "\nLAYANAN SURAT RESMI YANG TERSEDIA DI APLIKASI:";
            foreach ($context['surat_templates'] as $t) {
                $desc = $t['deskripsi'] ? " ({$t['deskripsi']})" : '';
                $lines[] = "- [{$t['kode_surat']}] {$t['nama_surat']}{$desc}";
            }
        } else {
            $lines[] = "\nLAYANAN SURAT: Saat ini belum ada template surat yang aktif di database.";
        }

        // 7. Data Status Pengajuan Surat
        if (! empty($context['pengajuan_status'])) {
            $ps = $context['pengajuan_status'];
            $lines[] = "\nDATA STATUS PENGAJUAN SURAT DARI DATABASE:";
            if ($ps['role'] === 'warga') {
                if ($ps['has_data'] && ! empty($ps['latest'])) {
                    $lat = $ps['latest'];
                    $lines[] = "- Pengajuan Terakhir: {$lat['nama_surat']}";
                    $lines[] = '- Nomor Surat: '.($lat['nomor_surat'] ?? 'Belum diterbitkan');
                    $lines[] = "- Status: {$lat['status']}";
                    $lines[] = "- Tanggal Pengajuan: {$lat['created_at']}";
                    if (! empty($lat['catatan'])) {
                        $lines[] = "- Catatan: {$lat['catatan']}";
                    }
                } else {
                    $lines[] = "- Riwayat: {$ps['message']}";
                }
            } else {
                // Rekap RT/RW/Admin
                $s = $ps['summary'];
                $lines[] = "- Rekap Wilayah: Total {$s['total']} surat ({$s['menunggu']} Menunggu/Diproses, {$s['selesai']} Selesai, {$s['ditolak']} Ditolak).";
                if (isset($s['butuh_tindakan'])) {
                    $lines[] = "- Surat yang saat ini butuh tindakan verifikasi/persetujuan oleh Anda: {$s['butuh_tindakan']} surat.";
                }
            }
        }

        // 7b. Statistik Surat Per Status (untuk pertanyaan hitungan "berapa surat selesai", "sedang diproses", "total berkas", dll)
        if (! empty($context['statistik_surat']) && ($context['statistik_surat']['has_data'] ?? false)) {
            $ss = $context['statistik_surat'];
            $lines[] = "\n========================================================";
            $lines[] = 'REKAPITULASI & STATISTIK PENGAJUAN SURAT (DATA REAL-TIME DATABASE):';
            $lines[] = '========================================================';
            $lines[] = "- Total Permohonan / Berkas Surat: {$ss['total']} berkas";
            $lines[] = "- Surat Sedang Diproses / Pending: {$ss['sedang_diproses']} berkas";
            $lines[] = "  * Menunggu Verifikasi RT: {$ss['menunggu_rt']} berkas";
            $lines[] = "  * Menunggu Persetujuan RW: {$ss['menunggu_rw']} berkas";
            $lines[] = "  * Sedang Diproses Petugas/Admin: {$ss['diproses']} berkas";
            $lines[] = "- Surat Selesai / Disetujui: {$ss['selesai']} berkas";
            $lines[] = "- Surat Ditolak: {$ss['ditolak']} berkas";
            $lines[] = "INSTRUKSI PENTING: JIKA PENGGUNA BERTANYA TENTANG JUMLAH/TOTAL SURAT, BERKAS, STATUS PERMOHONAN, ATAU 'YANG SEDANG DIPROSES', JAWAB SECARA SPESIFIK MENGGUNAKAN ANGKA REAL-TIME DI ATAS! JANGAN MEMBERIKAN JAWABAN TEMPLATE GENERIK (SEPERTI SYARAT KTP/KK ATAU ALUR PENGAJUAN) KETIKA DITANYA ANGKA ATAU STATUS!";
            $lines[] = "========================================================\n";
        } elseif (! empty($context['statistik_surat']) && ! ($context['statistik_surat']['has_data'] ?? true)) {
            $lines[] = "\nSTATISTIK SURAT: ".$context['statistik_surat']['message'];
        }

        // 8. Data Wilayah Administratif Desa (RW & RT)
        if (! empty($context['statistik_wilayah']) && ($context['statistik_wilayah']['has_data'] ?? false)) {
            $sw = $context['statistik_wilayah'];
            $lines[] = "\nDATA WILAYAH ADMINISTRATIF DESA (DATA FAKTUAL TABEL RW & RT):";
            $lines[] = "- Total Wilayah RW di Desa: {$sw['total_rw']} RW";
            $lines[] = "- Total Wilayah RT di Desa: {$sw['total_rt']} RT";
            if (! empty($sw['rws'])) {
                $rincian = [];
                foreach ($sw['rws'] as $rwItem) {
                    $rincian[] = "RW {$rwItem['nomor_rw']} ({$rwItem['total_rt']} RT)";
                }
                $lines[] = '- Rincian Pembagian Wilayah: '.implode(', ', $rincian);
            }
            $lines[] = 'PENTING: Angka ini adalah jumlah entitas wilayah fisik administratif desa, BUKAN jumlah akun pengguna (user).';
        }

        // 9. Data Akun Pengguna Berdasarkan Role
        if (! empty($context['statistik_akun']) && ($context['statistik_akun']['has_data'] ?? false)) {
            $sa = $context['statistik_akun'];
            if (! empty($sa['is_personal'])) {
                $lines[] = "\nDATA AKUN PENGGUNA ANDA:";
                $lines[] = "- Nama: {$sa['name']}, Email: {$sa['email']}, Username: ".($sa['username'] ?? '-').", Status: {$sa['status_aktif']}";
            } else {
                $lines[] = "\nDATA AKUN PENGGUNA SISTEM BERDASARKAN ROLE (DATA FAKTUAL TABEL USERS):";
                $lines[] = "- Total Akun Pengguna Terdaftar: {$sa['total']} akun ({$sa['active']} aktif)";
                $lines[] = "- Akun Role Super Admin: {$sa['super_admin']} akun";
                $lines[] = "- Akun Role RW: {$sa['rw']} akun (sebagai pengurus login dari {$sa['wilayah_rw']} wilayah RW)";
                $lines[] = "- Akun Role RT: {$sa['rt']} akun (sebagai pengurus login dari {$sa['wilayah_rt']} wilayah RT)";
                $lines[] = "- Akun Role Warga: {$sa['warga']} akun";
                $lines[] = 'PENTING: Jangan menyamakan jumlah akun pengguna role RW/RT dengan jumlah wilayah administratif RW/RT. Akun adalah login pengguna, sedangkan wilayah adalah pembagian desa.';
            }
        }

        // 10. Data Kepala Keluarga (KK)
        if (! empty($context['statistik_kk']) && ($context['statistik_kk']['has_data'] ?? false)) {
            $skk = $context['statistik_kk'];
            $lines[] = "\nDATA KEPALA KELUARGA & KARTU KELUARGA (DATA FAKTUAL):";
            $lines[] = "- Total Kartu Keluarga (KK Terpadu Dashboard & Sistem): {$skk['total_kk']} KK";
            $lines[] = "- Total Berkas Kartu Keluarga Terdaftar (tabel keluarga): {$skk['total_kk_terdaftar']} KK";
            $lines[] = "- Total Warga Berstatus Kepala Keluarga (tabel warga): {$skk['total_kepala_keluarga_warga']} orang";
            $lines[] = "- Total Nomor KK Unik pada Data Warga: {$skk['total_nomor_kk_unik']} nomor KK";
            if (! empty($skk['nama_kepala_keluarga'])) {
                $lines[] = "- Kepala Keluarga Tercatat: {$skk['nama_kepala_keluarga']}";
            }
        }

        // 11. Data Notifikasi In-App Surat
        if (! empty($context['notifikasi_data']) && ($context['notifikasi_data']['has_data'] ?? false)) {
            $notif = $context['notifikasi_data'];
            $lines[] = "\nDATA NOTIFIKASI SURAT RESMI (DATA FAKTUAL TABEL SURAT_NOTIFICATIONS):";
            $lines[] = "- Total Notifikasi Tercatat: {$notif['total']}";
            $lines[] = "- Notifikasi Terkirim (sent): {$notif['sent']}";
            $lines[] = '- Notifikasi Gagal/Pending: '.($notif['failed'] + $notif['pending']);
            if (! empty($notif['recent'])) {
                $lines[] = '- Riwayat Notifikasi Terbaru:';
                foreach ($notif['recent'] as $r) {
                    $lines[] = "  * [{$r['event_status']}] Status: {$r['status_kirim']} ({$r['sent_at']}): {$r['pesan_ringkas']}";
                }
            }
        }

        // 12. Data Riwayat WhatsApp Gateway (Fonnte)
        if (! empty($context['whatsapp_data']) && ($context['whatsapp_data']['has_data'] ?? false)) {
            $wa = $context['whatsapp_data'];
            $lines[] = "\nDATA RIWAYAT WHATSAPP GATEWAY FONNTE (DATA FAKTUAL LOG):";
            $lines[] = "- Total Siaran Pengumuman: {$wa['total_broadcasts']} broadcast";
            $lines[] = "- Total Penerima Ditargetkan: {$wa['total_recipients']} nomor";
            $lines[] = "- Pengiriman Aktual BERHASIL: {$wa['berhasil']} pesan";
            $lines[] = "- Pengiriman Aktual GAGAL: {$wa['gagal']} pesan";
            $lines[] = 'PENTING: Status pengiriman WhatsApp dibaca dari log aktual (berhasil/gagal). Jangan menganggap pembuatan data otomatis berarti pesan sampai jika log mencatat gagal/pending.';
        }

        // 13. Peta Fitur Aplikasi
        if (! empty($context['panduan_fitur_data']['features'])) {
            $lines[] = "\nPETA FITUR SISTEM PRAJADIGITAL:";
            foreach ($context['panduan_fitur_data']['features'] as $fKey => $f) {
                $lines[] = "- {$f['nama']}: {$f['deskripsi']} (Chatbot: {$f['kemampuan_chatbot']})";
            }
        }

        $lines[] = "\nPETUNJUK PENTING PADA PERHITUNGAN & JAWABAN:";
        $lines[] = '1. Jangan pernah menebak atau mengarang angka. Gunakan data faktual di atas.';
        $lines[] = '2. Bedakan dengan jelas antara jumlah entitas wilayah (RW/RT), jumlah akun login pengguna (users per role), dan jumlah penduduk riil (warga).';
        $lines[] = '3. Angka Kartu Keluarga (KK) harus konsisten 100% dengan metrik Dashboard.';

        return implode("\n", $lines);
    }
}
