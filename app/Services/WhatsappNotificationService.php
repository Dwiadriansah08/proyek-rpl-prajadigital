<?php

namespace App\Services;

use App\Http\Controllers\KonfigurasiController;
use App\Models\Informasi;
use App\Models\PengajuanSurat;
use App\Models\SuratNotification;
use App\Models\User;
use App\Models\Warga;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappRecipient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappNotificationService
{
    /**
     * Normalisasi nomor telepon ke format internasional (misal 0812 / +62812 / 812 -> 62812).
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '62')) {
            // Sudah diawali 62
            return $clean;
        }

        if (str_starts_with($clean, '0')) {
            return '62'.substr($clean, 1);
        }

        if (str_starts_with($clean, '8')) {
            return '62'.$clean;
        }

        return $clean;
    }

    /**
     * Validasi nomor telepon Indonesia (panjang 10-15 digit, diawali 628).
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $normalized = $this->normalizePhoneNumber($phone);

        return preg_match('/^628[0-9]{8,12}$/', $normalized) === 1;
    }

    /**
     * Cek apakah sistem sedang berada di mode simulasi.
     */
    public function isSimulationMode(): bool
    {
        $token = KonfigurasiController::resolveWaToken();
        $simulationConfig = KonfigurasiController::resolveWaSimulation();

        return empty($token) || $simulationConfig;
    }

    /**
     * Daftar host yang dianggap lokal/tidak dapat diakses oleh server eksternal seperti Fonnte.
     */
    private const LOCAL_HOSTS = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];

    /**
     * Periksa apakah sebuah URL menggunakan host yang bersifat lokal dan tidak bisa diakses dari internet.
     * Fonnte memerlukan URL publik yang dapat diunduh dari server mereka.
     */
    public function isLocalUrl(string $url): bool
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        // Cek apakah host adalah localhost, 127.x.x.x, atau private IP range
        if (in_array($host, self::LOCAL_HOSTS)) {
            return true;
        }

        // IPv4 loopback: 127.x.x.x
        if (preg_match('/^127\./', $host)) {
            return true;
        }

        // Hostname TLD .test, .local, .dev, .localhost — virtual host lokal (termasuk 'rpl.test')
        if (preg_match('/\.(test|local|dev|localhost|internal|lan)$/', $host)) {
            return true;
        }

        // Private IPv4 ranges: 10.x, 192.168.x, 172.16-31.x
        if (preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', $host)) {
            return true;
        }

        return false;
    }

    /**
     * Normalisasi URL atau file path PDF menjadi URL publik yang dapat diakses oleh gateway WhatsApp Fonnte.
     *
     * Prioritas resolusi:
     *   1. WHATSAPP_PUBLIC_URL di .env — override eksplisit untuk tunnel/ngrok/domain publik
     *   2. Jika $pdfUrl sudah berupa URL lengkap — pakai as-is (tapi tetap validasi apakah lokal)
     *   3. Jika $pdfUrl berupa absolute path lokal — konversi ke URL storage publik
     *   4. Jika $pdfUrl berupa nama file relatif — resolve ke storage/app/public
     */
    public function resolvePublicPdfUrl(?string $pdfUrl): ?string
    {
        if (empty($pdfUrl)) {
            return null;
        }

        // 1. Cek override WHATSAPP_PUBLIC_URL di .env — berguna saat menggunakan Ngrok/Expose/Tunnel
        $publicBaseOverride = rtrim((string) config('services.whatsapp.public_url', ''), '/');

        // 2. Jika $pdfUrl sudah berupa URL HTTP/HTTPS yang valid
        if (filter_var($pdfUrl, FILTER_VALIDATE_URL)) {
            // Jika ada override public base URL, replace base URL-nya agar server Fonnte bisa akses
            if (! empty($publicBaseOverride)) {
                $appUrl = rtrim(config('app.url'), '/');
                if (str_starts_with($pdfUrl, $appUrl)) {
                    $resolved = $publicBaseOverride.substr($pdfUrl, strlen($appUrl));

                    return $resolved;
                }
            }

            return $pdfUrl;
        }

        // 3. Jika $pdfUrl berupa absolute path lokal di filesystem
        if (file_exists($pdfUrl)) {
            $publicPath = storage_path('app/public');
            if (str_starts_with(realpath($pdfUrl), realpath($publicPath))) {
                $relative = ltrim(substr(realpath($pdfUrl), strlen(realpath($publicPath))), '\\/');
                $baseUrl = ! empty($publicBaseOverride) ? $publicBaseOverride : rtrim(config('app.url'), '/');

                return $baseUrl.'/storage/'.str_replace('\\', '/', $relative);
            }

            return url($pdfUrl);
        }

        // 4. Jika berupa nama file di storage/app/public
        $baseUrl = ! empty($publicBaseOverride) ? $publicBaseOverride : rtrim(config('app.url'), '/');
        if (file_exists(storage_path('app/public/'.$pdfUrl))) {
            return $baseUrl.'/storage/'.ltrim($pdfUrl, '/');
        }

        return $baseUrl.'/storage/'.ltrim($pdfUrl, '/');
    }

    /**
     * Kirim pesan notifikasi WhatsApp atau simulasikan jika belum dikonfigurasi.
     * Mendukung pengiriman media/dokumen file (misal PDF) jika $pdfUrl disertakan.
     *
     * PENTING — Syarat lampiran PDF via Fonnte:
     *   - Parameter `url` harus berisi URL publik yang dapat diakses server Fonnte dari internet.
     *   - URL localhost / 127.0.0.1 / *.test / *.local TIDAK AKAN berhasil diunduh Fonnte.
     *   - Gunakan WHATSAPP_PUBLIC_URL di .env saat development lokal (Ngrok / Expose / tunnel lain).
     */
    public function sendNotification(
        string $recipientPhone,
        string $message,
        ?int $suratId = null,
        ?string $pdfUrl = null,
        ?string $filename = null
    ): array {
        if (! $this->isValidPhoneNumber($recipientPhone)) {
            return [
                'status' => 'failed',
                'success' => false,
                'message' => 'Format nomor WhatsApp tidak valid.',
            ];
        }

        $normalizedPhone = $this->normalizePhoneNumber($recipientPhone);
        $token = KonfigurasiController::resolveWaToken();
        $endpoint = config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');

        $resolvedPdfUrl = $this->resolvePublicPdfUrl($pdfUrl);
        if ($resolvedPdfUrl && empty($filename)) {
            $filename = basename(parse_url($resolvedPdfUrl, PHP_URL_PATH)) ?: 'Surat.pdf';
        }

        // ─── DIAGNOSTIC: Peringatan jika URL PDF tidak dapat diakses dari internet ───────
        if (! empty($resolvedPdfUrl) && $this->isLocalUrl($resolvedPdfUrl)) {
            Log::warning('[WA PDF] URL PDF bersifat LOKAL — Fonnte tidak dapat mengunduh file ini!', [
                'surat_id' => $suratId,
                'pdf_url' => $resolvedPdfUrl,
                'solusi' => 'Set WHATSAPP_PUBLIC_URL di .env dengan URL publik (Ngrok/tunnel/domain hosting).',
                'contoh_env' => 'WHATSAPP_PUBLIC_URL=https://xxxx.ngrok-free.app',
            ]);
        }

        if ($this->isSimulationMode()) {
            Log::info('[SIMULASI WA] Notifikasi WhatsApp', [
                'surat_id' => $suratId,
                'target' => $normalizedPhone,
                'pesan' => $message,
                'pdf_url' => $resolvedPdfUrl,
                'url_is_local' => ! empty($resolvedPdfUrl) ? $this->isLocalUrl($resolvedPdfUrl) : null,
                'filename' => $filename,
                'status' => 'SIMULATED_SUCCESS',
            ]);

            return [
                'status' => 'simulated',
                'success' => true,
                'message' => 'Notifikasi WhatsApp berhasil disimulasikan (Mode Simulasi aktif).',
            ];
        }

        try {
            $payload = [
                'target' => $normalizedPhone,
                'message' => $message,
            ];

            if (! empty($resolvedPdfUrl)) {
                $payload['url'] = $resolvedPdfUrl;
                $payload['filename'] = ! empty($filename) ? $filename : 'Surat.pdf';
            }

            // ─── DIAGNOSTIC LOG sebelum kirim ke Fonnte ─────────────────────────────────
            Log::info('[WA] Mengirim notifikasi ke Fonnte', [
                'surat_id' => $suratId,
                'target' => $normalizedPhone,
                'endpoint' => $endpoint,
                'has_pdf' => ! empty($resolvedPdfUrl),
                'pdf_url' => $resolvedPdfUrl ?? null,
                'filename' => $payload['filename'] ?? null,
                'url_is_local' => ! empty($resolvedPdfUrl) ? $this->isLocalUrl($resolvedPdfUrl) : false,
                'payload_keys' => array_keys($payload),
            ]);

            // Gunakan asForm() (application/x-www-form-urlencoded) sesuai spesifikasi resmi API Fonnte
            $response = Http::asForm()->timeout(20)->withHeaders([
                'Authorization' => $token,
            ])->post($endpoint, $payload);

            $json = $response->json();
            $isStatusTrue = isset($json['status']) && ($json['status'] === true || $json['status'] === 'true');
            $isOk = $response->successful() && $isStatusTrue;

            $responseMsg = $json['reason'] ?? ($json['message'] ?? ($isOk ? 'Terkirim via Fonnte' : 'Gagal mengirim melalui Fonnte'));

            if ($isOk) {
                Log::info('[SUKSES WA] Notifikasi WhatsApp berhasil dikirim via Fonnte', [
                    'surat_id' => $suratId,
                    'target' => $normalizedPhone,
                    'has_pdf' => ! empty($resolvedPdfUrl),
                    'pdf_url' => $resolvedPdfUrl,
                    'response' => $json,
                ]);
            } else {
                Log::error('[ERROR WA] Gagal mengirim notifikasi via Fonnte', [
                    'surat_id' => $suratId,
                    'target' => $normalizedPhone,
                    'http_status' => $response->status(),
                    'response' => $json,
                    'has_pdf' => ! empty($resolvedPdfUrl),
                    'pdf_url' => $resolvedPdfUrl,
                    'url_is_local' => ! empty($resolvedPdfUrl) ? $this->isLocalUrl($resolvedPdfUrl) : false,
                ]);
            }

            return [
                'status' => $isOk ? 'sent' : 'failed',
                'success' => $isOk,
                'response' => $json,
                'message' => $responseMsg,
            ];
        } catch (\Throwable $e) {
            Log::error('[ERROR WA] Gagal menghubungi gateway WhatsApp Fonnte', [
                'error' => $e->getMessage(),
                'target' => $normalizedPhone,
                'surat_id' => $suratId,
                'has_pdf' => ! empty($resolvedPdfUrl),
                'pdf_url' => $resolvedPdfUrl ?? null,
            ]);

            return [
                'status' => 'error',
                'success' => false,
                'message' => 'Koneksi ke gateway WhatsApp gagal: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Kirim notifikasi siklus pengajuan surat ke penerima berwenang (RT, RW, Warga, Petugas).
     * Mencegah notifikasi ganda melalui idempotensi tabel surat_notifications,
     * mencatat status pengiriman, dan tidak membatalkan alur surat jika terjadi kegagalan WA.
     */
    public function notifyStatusUpdate(PengajuanSurat $surat, string $status, ?string $pdfUrl = null): void
    {
        $surat->loadMissing(['warga.rt', 'warga.rw', 'warga.user', 'template']);
        $warga = $surat->warga;
        if (! $warga) {
            return;
        }

        $namaSurat = $surat->template?->nama_surat ?? 'Surat Pengantar';
        $nomorSurat = $surat->nomor_surat ?? '-';
        $waktu = now()->translatedFormat('d F Y H:i');

        $recipients = collect();

        // 1. Saat warga baru mengajukan surat (MENUNGGU_RT): Beritahu Ketua RT terkait
        if ($status === 'MENUNGGU_RT') {
            $rtUsers = User::whereHas('role', fn ($q) => $q->where('name', 'rt'))
                ->where('rt_id', $warga->rt_id)
                ->where('is_active', true)
                ->get();

            foreach ($rtUsers as $u) {
                $phone = $u->nomor_telepon ?: $u->warga?->nomor_telepon;
                if ($phone && $this->isValidPhoneNumber($phone)) {
                    $pesan = "*PEMBERITAHUAN PENGAJUAN SURAT (RT)*\n\n"
                        ."Halo Bapak/Ibu {$u->name} (Ketua/Pengurus RT),\n"
                        ."Ada permohonan surat baru yang butuh verifikasi Anda:\n\n"
                        ."- Pemohon: {$warga->nama_lengkap}\n"
                        ."- Jenis Surat: {$namaSurat}\n"
                        ."- Status: MENUNGGU VERIFIKASI RT\n"
                        ."- Waktu Pengajuan: {$waktu}\n\n"
                        ."Silakan login ke sistem PrajaDigital untuk meninjau dan memproses pengajuan ini.\n"
                        .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                    $recipients->push([
                        'role' => 'rt',
                        'user_id' => $u->id,
                        'warga_id' => $u->warga?->id,
                        'phone' => $this->normalizePhoneNumber($phone),
                        'message' => $pesan,
                    ]);
                }
            }
        }

        // 2. Saat RT menyetujui dan diteruskan ke RW (MENUNGGU_RW): Beritahu RW terkait & Warga
        elseif ($status === 'MENUNGGU_RW') {
            // A. Notifikasi ke Pengurus RW terkait
            $rwUsers = User::whereHas('role', fn ($q) => $q->where('name', 'rw'))
                ->where('rw_id', $warga->rw_id)
                ->where('is_active', true)
                ->get();

            foreach ($rwUsers as $u) {
                $phone = $u->nomor_telepon ?: $u->warga?->nomor_telepon;
                if ($phone && $this->isValidPhoneNumber($phone)) {
                    $pesan = "*PEMBERITAHUAN VERIFIKASI SURAT (RW)*\n\n"
                        ."Halo Bapak/Ibu {$u->name} (Ketua/Pengurus RW),\n"
                        ."Surat telah disetujui oleh RT dan kini menunggu persetujuan Anda:\n\n"
                        ."- Pemohon: {$warga->nama_lengkap}\n"
                        ."- Jenis Surat: {$namaSurat}\n"
                        ."- Status: MENUNGGU PERSETUJUAN RW\n"
                        ."- Waktu: {$waktu}\n\n"
                        ."Silakan login ke sistem PrajaDigital untuk menyetujui dan menerbitkan dokumen resmi.\n"
                        .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                    $recipients->push([
                        'role' => 'rw',
                        'user_id' => $u->id,
                        'warga_id' => $u->warga?->id,
                        'phone' => $this->normalizePhoneNumber($phone),
                        'message' => $pesan,
                    ]);
                }
            }

            // B. Notifikasi ke Warga Pemohon
            $wargaPhone = $warga->nomor_telepon ?: $warga->user?->nomor_telepon;
            if ($wargaPhone && $this->isValidPhoneNumber($wargaPhone)) {
                $pesanWarga = "*STATUS PENGAJUAN SURAT*\n\n"
                    ."Halo {$warga->nama_lengkap},\n"
                    ."Kabar baik! Pengajuan surat Anda telah *DISETUJUI OLEH RT* dan sedang diteruskan untuk verifikasi RW:\n\n"
                    ."- Jenis Surat: {$namaSurat}\n"
                    ."- Status: MENUNGGU PERSETUJUAN RW\n"
                    ."- Waktu: {$waktu}\n\n"
                    ."Anda akan menerima pemberitahuan kembali setelah surat selesai disetujui RW.\n"
                    .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                $recipients->push([
                    'role' => 'warga',
                    'user_id' => $warga->user?->id,
                    'warga_id' => $warga->id,
                    'phone' => $this->normalizePhoneNumber($wargaPhone),
                    'message' => $pesanWarga,
                ]);
            }
        }

        // 2b. Saat RW menyetujui dan diteruskan ke Admin Desa (MENUNGGU_ADMIN_DESA): Beritahu Petugas Kantor Desa & Warga
        elseif ($status === 'MENUNGGU_ADMIN_DESA') {
            // A. Notifikasi ke Admin Desa / Super Admin
            $adminUsers = User::whereHas('role', fn ($q) => $q->whereIn('name', ['admin_desa', 'super_admin']))
                ->where('is_active', true)
                ->get();

            foreach ($adminUsers as $u) {
                $phone = $u->nomor_telepon ?: $u->warga?->nomor_telepon;
                if ($phone && $this->isValidPhoneNumber($phone)) {
                    $pesan = "*PEMBERITAHUAN VERIFIKASI DESA*\n\n"
                        ."Halo Bapak/Ibu {$u->name} (Admin Desa),\n"
                        ."Surat telah disetujui RW dan membutuhkan verifikasi serta penerbitan akhir oleh kantor desa:\n\n"
                        ."- Pemohon: {$warga->nama_lengkap}\n"
                        ."- Jenis Surat: {$namaSurat}\n"
                        ."- Status: MENUNGGU VERIFIKASI DESA\n"
                        ."- Waktu: {$waktu}\n\n"
                        ."Silakan login ke sistem PrajaDigital untuk memverifikasi dan menerbitkan surat resmi.\n"
                        .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                    $recipients->push([
                        'role' => 'admin_desa',
                        'user_id' => $u->id,
                        'warga_id' => $u->warga?->id,
                        'phone' => $this->normalizePhoneNumber($phone),
                        'message' => $pesan,
                    ]);
                }
            }

            // B. Notifikasi ke Warga Pemohon
            $wargaPhone = $warga->nomor_telepon ?: $warga->user?->nomor_telepon;
            if ($wargaPhone && $this->isValidPhoneNumber($wargaPhone)) {
                $pesanWarga = "*STATUS PENGAJUAN SURAT*\n\n"
                    ."Halo {$warga->nama_lengkap},\n"
                    ."Kabar baik! Pengajuan surat Anda telah *DISETUJUI OLEH RW* dan saat ini sedang dalam proses *VERIFIKASI & PENERBITAN AKHIR OLEH KANTOR DESA*:\n\n"
                    ."- Jenis Surat: {$namaSurat}\n"
                    ."- Status: MENUNGGU VERIFIKASI DESA\n"
                    ."- Waktu: {$waktu}\n\n"
                    ."Anda akan menerima pemberitahuan dan dokumen PDF setelah surat resmi selesai diterbitkan oleh pihak desa.\n"
                    .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                $recipients->push([
                    'role' => 'warga',
                    'user_id' => $warga->user?->id,
                    'warga_id' => $warga->id,
                    'phone' => $this->normalizePhoneNumber($wargaPhone),
                    'message' => $pesanWarga,
                ]);
            }
        }

        // 3. Saat RW menyetujui atau proses selesai (SELESAI): Beritahu Warga pemohon via pesan teks (PDF dikirim via Email)
        elseif ($status === 'SELESAI') {
            $wargaPhone = $warga->nomor_telepon ?: $warga->user?->nomor_telepon;
            if ($wargaPhone && $this->isValidPhoneNumber($wargaPhone)) {
                $wargaEmail = $warga->email ?: ($warga->user?->email ?: 'email Anda');

                if (! empty($pdfUrl)) {
                    $namaSuratClean = preg_replace('/^surat[_\-\s]+/i', '', $namaSurat);
                    $slugSurat = Str::studly($namaSuratClean ?: $namaSurat);
                    $slugWarga = Str::studly($warga->nama_lengkap);
                    $filename = "Surat_{$slugSurat}_{$slugWarga}.pdf";

                    $pesanWarga = "*PENGAJUAN SURAT SELESAI*\n\n"
                        ."Halo {$warga->nama_lengkap},\n"
                        ."Selamat, pengajuan surat {$namaSurat} Anda telah *SELESAI DIPROSES DAN DISETUJUI*:\n\n"
                        ."- Nomor Surat: {$nomorSurat}\n"
                        ."- Jenis Surat: {$namaSurat}\n"
                        ."- Status: SELESAI\n"
                        ."- Waktu Selesai: {$waktu}\n\n"
                        ."File PDF surat resmi dapat Anda unduh pada lampiran dokumen di bawah ini.\n\n"
                        ."Terima kasih!\n"
                        .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                    $recipients->push([
                        'role' => 'warga',
                        'user_id' => $warga->user?->id,
                        'warga_id' => $warga->id,
                        'phone' => $this->normalizePhoneNumber($wargaPhone),
                        'message' => $pesanWarga,
                        'pdf_url' => $pdfUrl,
                        'filename' => $filename,
                    ]);
                } else {
                    $pesanWarga = "*PENGAJUAN SURAT SELESAI*\n\n"
                        ."Halo {$warga->nama_lengkap},\n"
                        ."Selamat, pengajuan {$namaSurat} Anda telah *SELESAI DIPROSES DAN DISETUJUI*:\n\n"
                        ."- Nomor Surat: {$nomorSurat}\n"
                        ."- Jenis Surat: {$namaSurat}\n"
                        ."- Status: SELESAI\n"
                        ."- Waktu Selesai: {$waktu}\n\n"
                        ."File PDF dokumen resmi telah kami kirimkan ke email Anda ({$wargaEmail}). Silakan periksa inbox/spam email Anda atau unduh langsung di sistem PrajaDigital.\n\n"
                        ."Terima kasih!\n"
                        .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                    $recipients->push([
                        'role' => 'warga',
                        'user_id' => $warga->user?->id,
                        'warga_id' => $warga->id,
                        'phone' => $this->normalizePhoneNumber($wargaPhone),
                        'message' => $pesanWarga,
                    ]);
                }
            }
        }

        // 4. Saat surat ditolak di tahap mana pun (DITOLAK): Beritahu Warga beserta alasan penolakan
        elseif ($status === 'DITOLAK') {
            $wargaPhone = $warga->nomor_telepon ?: $warga->user?->nomor_telepon;
            if ($wargaPhone && $this->isValidPhoneNumber($wargaPhone)) {
                $alasan = ! empty($surat->catatan) ? $surat->catatan : 'Tidak ada catatan tambahan dari petugas.';
                $pesanWarga = "*PEMBERITAHUAN PENGAJUAN SURAT DITOLAK*\n\n"
                    ."Halo {$warga->nama_lengkap},\n"
                    ."Mohon maaf, pengajuan surat Anda belum dapat disetujui:\n\n"
                    ."- Jenis Surat: {$namaSurat}\n"
                    ."- Status: DITOLAK\n"
                    ."- Waktu: {$waktu}\n"
                    ."- Alasan Penolakan: {$alasan}\n\n"
                    ."Silakan perbaiki persyaratan yang diperlukan lalu ajukan kembali melalui sistem PrajaDigital.\n"
                    .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

                $recipients->push([
                    'role' => 'warga',
                    'user_id' => $warga->user?->id,
                    'warga_id' => $warga->id,
                    'phone' => $this->normalizePhoneNumber($wargaPhone),
                    'message' => $pesanWarga,
                ]);
            }
        }

        try {
            // Eksekusi pengiriman dengan idempotensi unik per pengajuan_surat_id + event_status + recipient_phone
            foreach ($recipients->unique('phone') as $item) {
                $this->dispatchSuratNotification(
                    $surat->id,
                    $status,
                    $item['role'],
                    $item['user_id'],
                    $item['warga_id'],
                    $item['phone'],
                    $item['message'],
                    $item['pdf_url'] ?? null,
                    $item['filename'] ?? null
                );
            }
        } catch (\Throwable $e) {
            Log::error('[ERROR SURAT WA] Gagal memproses seluruh notifikasi status surat', [
                'surat_id' => $surat->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mengirim notifikasi surat ke satu penerima secara aman dan mencatat audit ke tabel surat_notifications.
     */
    protected function dispatchSuratNotification(
        int $suratId,
        string $status,
        string $role,
        ?int $userId,
        ?int $wargaId,
        string $phone,
        string $message,
        ?string $pdfUrl = null,
        ?string $filename = null
    ): void {
        // Cek apakah notifikasi untuk transisi status ini sudah pernah berhasil atau dibuat
        $existing = SuratNotification::where('pengajuan_surat_id', $suratId)
            ->where('event_status', $status)
            ->where('recipient_phone', $phone)
            ->first();

        if ($existing && in_array($existing->status, ['sent', 'simulated', 'berhasil'])) {
            // Sudah pernah terkirim, hindari notifikasi ganda
            return;
        }

        $record = $existing ?? SuratNotification::create([
            'pengajuan_surat_id' => $suratId,
            'event_status' => $status,
            'recipient_role' => $role,
            'recipient_user_id' => $userId,
            'recipient_warga_id' => $wargaId,
            'recipient_phone' => $phone,
            'message' => $message,
            'status' => 'menunggu',
        ]);

        try {
            $result = $this->sendNotification($phone, $message, $suratId, $pdfUrl, $filename);

            if ($result['success']) {
                $statusResult = $this->isSimulationMode() ? 'simulated' : 'sent';
                $record->update([
                    'status' => $statusResult,
                    'response_payload' => isset($result['response']) ? json_encode($result['response']) : ($result['message'] ?? 'OK'),
                    'sent_at' => now(),
                ]);
            } else {
                $record->update([
                    'status' => 'failed',
                    'error_message' => $result['message'] ?? 'Gagal mengirim pesan',
                    'response_payload' => isset($result['response']) ? json_encode($result['response']) : null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[ERROR SURAT WA] Exception saat mengirim notifikasi surat', [
                'surat_id' => $suratId,
                'phone' => $phone,
                'has_pdf' => ! empty($pdfUrl),
                'error' => $e->getMessage(),
            ]);

            $record->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Kirim notifikasi surat selesai beserta lampiran file PDF langsung ke WhatsApp warga pemohon.
     */
    public function sendSuratSelesaiNotification(PengajuanSurat $surat, ?string $pdfUrl = null): array
    {
        $this->notifyStatusUpdate($surat, 'SELESAI', $pdfUrl);

        $notif = SuratNotification::where('pengajuan_surat_id', $surat->id)
            ->where('event_status', 'SELESAI')
            ->latest()
            ->first();

        return [
            'status' => $notif?->status ?? 'sent',
            'success' => in_array($notif?->status, ['sent', 'simulated', 'berhasil']),
            'message' => $notif?->error_message ?? ($notif?->response_payload ?? 'Notifikasi selesai diproses'),
        ];
    }

    /**
     * Resolusi kandidat penerima WhatsApp berdasarkan konfigurasi target pengumuman.
     * Mengembalikan collection berisi [warga_id, user_id, nama, nomor_telepon].
     * Nomor telepon dinormalisasi dan dide-duplikasi secara ketat.
     */
    public function resolveRecipients(Informasi $informasi): Collection
    {
        $candidates = collect();

        // Mode Role Tertentu
        if ($informasi->target_type === 'role' && $informasi->target_role) {
            $roleName = $informasi->target_role;
            $users = User::with('warga')->whereHas('role', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            })->where('is_active', true)->get();

            foreach ($users as $u) {
                $phone = $u->nomor_telepon ?: $u->warga?->nomor_telepon;
                if ($phone && $this->isValidPhoneNumber($phone)) {
                    $candidates->push([
                        'warga_id' => $u->warga?->id,
                        'user_id' => $u->id,
                        'nama' => $u->name,
                        'nomor_telepon' => $this->normalizePhoneNumber($phone),
                    ]);
                }
            }

            return $candidates->unique('nomor_telepon')->values();
        }

        // Mode Wilayah (Desa, RW, RT)
        $query = Warga::query()->with('user')->where('status_aktif', true);

        if ($informasi->target_type === 'rt' || $informasi->rt_id) {
            $query->where('rt_id', $informasi->rt_id);
            if ($informasi->rw_id) {
                $query->where('rw_id', $informasi->rw_id);
            }
        } elseif ($informasi->target_type === 'rw' || $informasi->rw_id) {
            $query->where('rw_id', $informasi->rw_id);
        }

        $wargas = $query->get();

        foreach ($wargas as $w) {
            $phone = $w->nomor_telepon ?: $w->user?->nomor_telepon;
            if (! empty($phone) && $this->isValidPhoneNumber($phone)) {
                $candidates->push([
                    'warga_id' => $w->id,
                    'user_id' => $w->user?->id,
                    'nama' => $w->nama_lengkap,
                    'nomor_telepon' => $this->normalizePhoneNumber($phone),
                ]);
            }
        }

        return $candidates->unique('nomor_telepon')->values();
    }

    /**
     * Eksekusi pengiriman broadcast WhatsApp untuk pengumuman.
     * Menerapkan deduping, rate limiting (delay), dan logging per-recipient.
     */
    public function executeBroadcast(Informasi $informasi, User $sender): WhatsappBroadcast
    {
        $recipients = $this->resolveRecipients($informasi);

        // Cari nomor yang sudah pernah menerima pengumuman ini agar tidak double-send
        $alreadySentPhones = WhatsappRecipient::where('informasi_id', $informasi->id)
            ->where('status', 'berhasil')
            ->pluck('recipient_phone')
            ->toArray();

        // Filter kandidat yang belum pernah menerima
        $pendingRecipients = $recipients->reject(function ($item) use ($alreadySentPhones) {
            return in_array($item['nomor_telepon'], $alreadySentPhones);
        });

        $targetDesc = match ($informasi->target_type) {
            'role' => 'Role: '.strtoupper($informasi->target_role ?? 'Semua'),
            'rt' => 'RT '.($informasi->rt?->nomor_rt ?? '-').' / RW '.($informasi->rw?->nomor_rw ?? '-'),
            'rw' => 'RW '.($informasi->rw?->nomor_rw ?? '-'),
            default => 'Seluruh Warga Desa',
        };

        $broadcast = WhatsappBroadcast::create([
            'informasi_id' => $informasi->id,
            'sender_id' => $sender->id,
            'target_description' => $targetDesc,
            'total_recipients' => $pendingRecipients->count(),
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'completed',
        ]);

        $successCount = 0;
        $failedCount = 0;
        $firstError = null;

        $judul = $informasi->judul;
        $isiRingkas = Str::limit(strip_tags($informasi->isi ?? $informasi->konten), 300);

        foreach ($pendingRecipients as $r) {
            $nama = $r['nama'];
            $pesan = "*PENGUMUMAN DESA PRAJADIGITAL*\n\n"
                   ."Halo {$nama},\n"
                   ."*{$judul}*\n\n"
                   ."{$isiRingkas}\n\n"
                   ."Detail lengkap dapat diakses pada portal resmi PrajaDigital.\n"
                   .'_Pesan otomatis dari Pelayanan Desa PrajaDigital._';

            // Simpan status awal 'menunggu'
            $recipientRecord = WhatsappRecipient::create([
                'broadcast_id' => $broadcast->id,
                'informasi_id' => $informasi->id,
                'warga_id' => $r['warga_id'],
                'user_id' => $r['user_id'],
                'recipient_name' => $r['nama'],
                'recipient_phone' => $r['nomor_telepon'],
                'status' => 'menunggu',
            ]);

            $result = $this->sendNotification($r['nomor_telepon'], $pesan);

            if ($result['success']) {
                $successCount++;
                $recipientRecord->update([
                    'status' => 'berhasil',
                    'response_payload' => isset($result['response']) ? json_encode($result['response']) : 'Simulasi Sukses',
                    'sent_at' => now(),
                ]);
            } else {
                $failedCount++;
                if (! $firstError) {
                    $firstError = $result['message'] ?? 'Gateway Failure';
                }
                $recipientRecord->update([
                    'status' => 'gagal',
                    'error_message' => $result['message'] ?? 'Gagal mengirim pesan',
                    'response_payload' => isset($result['response']) ? json_encode($result['response']) : null,
                ]);
            }

            // Rate-limiting perlindungan: jeda 100ms jika pengiriman live
            if (! $this->isSimulationMode()) {
                usleep(100000);
            }
        }

        $broadcast->update([
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'error_summary' => $firstError,
            'status' => ($failedCount > 0 && $successCount === 0) ? 'failed' : 'completed',
        ]);

        return $broadcast;
    }
}
