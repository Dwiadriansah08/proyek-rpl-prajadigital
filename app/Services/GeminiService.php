<?php

namespace App\Services;

use App\Http\Controllers\KonfigurasiController;
use App\Models\Desa;
use App\Models\PengajuanSurat;
use App\Models\Role;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * Timeout request ke Gemini API (detik).
     */
    public const REQUEST_TIMEOUT = 35;

    /**
     * Model default Gemini resmi, gratis, aktif & berkinerja tinggi.
     */
    public const DEFAULT_MODEL = 'gemini-3.5-flash-lite';

    public function __construct(
        protected ?AiChatContextService $contextService = null
    ) {
        $this->contextService = $contextService ?? app(AiChatContextService::class);
    }

    /**
     * Membangun instruksi sistem dengan injeksi real-time data kependudukan, statistik surat & identitas pengguna.
     */
    public function buildSystemInstruction(?User $user = null, ?string $userMessage = null): string
    {
        $desa = Desa::first();
        $desaNama = $desa?->nama ?? 'Desa Lemahabang';

        // 1. Identitas User Aktif (Logged-in User)
        $roleName = $user && $user->role ? strtolower($user->role->name) : 'warga';
        $roleTitle = match ($roleName) {
            'super_admin' => 'SUPER_ADMIN',
            'admin_desa' => 'ADMIN_DESA',
            'rw' => 'KETUA_RW',
            'rt' => 'KETUA_RT',
            default => 'WARGA',
        };

        $nik = $user?->warga?->nik ?? $user?->nik ?? 'Tidak terdaftar';
        $userInfo = $user ? [
            'nama' => $user->name,
            'email' => $user->email ?? '-',
            'role' => $roleTitle,
            'nik' => $nik,
        ] : null;

        // 2. Statistik Real-Time Database Kependudukan & Role
        $userStats = Role::withCount('users')->pluck('users_count', 'name')->toArray();
        $totalWargaRole = $userStats['warga'] ?? 0;
        $totalPenduduk = Warga::count();
        if ($totalPenduduk === 0) {
            $totalPenduduk = $totalWargaRole;
        }

        $adminDesaList = User::whereHas('role', fn ($q) => $q->where('name', 'admin_desa'))->pluck('name')->implode(', ');
        $superAdminList = User::whereHas('role', fn ($q) => $q->where('name', 'super_admin'))->pluck('name')->implode(', ');
        $totalAdminDesa = $userStats['admin_desa'] ?? 0;
        $totalSuperAdmin = $userStats['super_admin'] ?? 0;
        $totalRt = $userStats['rt'] ?? 0;
        $totalRw = $userStats['rw'] ?? 0;

        // Statistik Pengajuan Surat Real-Time
        $totalSurat = PengajuanSurat::count();
        $suratMenungguRT = PengajuanSurat::where('status', 'MENUNGGU_RT')->count();
        $suratMenungguRW = PengajuanSurat::where('status', 'MENUNGGU_RW')->count();
        $suratMenungguAdmin = PengajuanSurat::where('status', 'MENUNGGU_ADMIN_DESA')->count();
        $suratSelesai = PengajuanSurat::where('status', 'SELESAI')->count();

        // Data wilayah pimpinan
        $rwFirst = Rw::first();
        $rtFirst = Rt::first();
        $rwKetuaName = $rwFirst?->ketua_rw ?? 'Yono';
        $rtKetuaName = $rtFirst?->ketua_rt ?? 'YANTO SUJONO';
        $rwNomor = $rwFirst?->nomor_rw ?? '001';
        $rtNomor = $rtFirst?->nomor_rt ?? '001';
        $wilayahSummary = "RW {$rwNomor} dipimpin Bapak {$rwKetuaName}, RT {$rtNomor} dipimpin Bapak {$rtKetuaName}.";

        // Konteks wilayah spesifik user yang sedang login
        $wilayah = '';
        if ($roleName === 'rw') {
            $nomorRw = optional($user->rw)->nomor_rw ?? $rwNomor;
            $wilayah = "Pengguna mengelola RW {$nomorRw}.";
        } elseif ($roleName === 'rt') {
            $nomorRw = optional($user->rw)->nomor_rw ?? $rwNomor;
            $nomorRt = optional($user->rt)->nomor_rt ?? $rtNomor;
            $wilayah = "Pengguna mengelola RW {$nomorRw} RT {$nomorRt}.";
        } elseif ($roleName === 'warga') {
            $warga = $user?->warga;
            $nomorRw = optional(optional($warga)->rw)->nomor_rw ?? $rwNomor;
            $nomorRt = optional(optional($warga)->rt)->nomor_rt ?? $rtNomor;
            $wilayah = "Pengguna adalah warga RW {$nomorRw} RT {$nomorRt}.";
        } elseif ($roleName === 'admin_desa') {
            $wilayah = "Pengguna adalah Admin Desa (Lurah/Staf Kantor {$desaNama}) dan memiliki wewenang verifikasi akhir surat serta manajemen pelayanan desa.";
        } elseif ($roleName === 'super_admin') {
            $wilayah = 'Pengguna adalah Super Admin dan memiliki akses administrasi tingkat desa.';
        }

        // Ambil konteks database terotorisasi dari AiChatContextService jika tersedia
        $dbContextData = '';
        if ($this->contextService && $user && $userMessage) {
            $contextArray = $this->contextService->getContextForMessage($user, $userMessage);
            $dbContextData = $this->contextService->formatContextForSystemPrompt($contextArray);
        }

        $adminDesaText = $adminDesaList ?: 'Belum ada';
        $superAdminText = $superAdminList ?: 'Belum ada';

        $userName = $userInfo['nama'] ?? 'Tamu';
        $userRole = $userInfo['role'] ?? 'WARGA';
        $userEmail = $userInfo['email'] ?? '-';
        $userNik = $userInfo['nik'] ?? 'Tidak terdaftar';

        return <<<CONTEXT
Kamu adalah AI Assistant {$desaNama} ('PrajaDigital') yang cerdas, ramah, dan solutif.
KONTEKS PENGGUNA YANG SEDANG CHAT SAMA KAMU:
- Nama: {$userName}
- Role: {$userRole}
- Email: {$userEmail}
- NIK: {$userNik}

KONTEKS REAL-TIME DATA {$desaNama}:
KONTEKS REAL-TIME DATABASE {$desaNama}:
- Pengguna Logged-In Saat Ini: {$userName} (Role: {$userRole})
- Total Warga: {$totalPenduduk} jiwa
- Total Akun Admin Desa: {$totalAdminDesa} (Nama: {$adminDesaText})
- Total Akun Super Admin: {$totalSuperAdmin} (Nama: {$superAdminText})
- Daftar Admin Desa: {$adminDesaText} (Total: {$totalAdminDesa})
- Daftar Super Admin: {$superAdminText} (Total: {$totalSuperAdmin})
- Total Akun RT: {$totalRt}, RW: {$totalRw}, Warga: {$totalWargaRole}.
- Statistik Pengajuan Surat: Total {$totalSurat} (Menunggu RT: {$suratMenungguRT}, Menunggu RW: {$suratMenungguRW}, Menunggu Admin Desa: {$suratMenungguAdmin}, Selesai: {$suratSelesai})
- Wilayah: {$wilayahSummary}

ATURAN MENJAWAB:
1. Jika user bertanya 'siapa saya', 'siapa nama saya', atau 'apa role saya', JAWAB SESUAI DATA PENGGUNA LOGGED-IN DI ATAS.
2. Gunakan format Markdown yang rapi (gunakan bold, bullet list, dan baris baru / paragraf terpisah).
3. Jawab pertanyaan warga secara ramah, presisi, dan akurat sesuai data desa di atas.

ANDA ADALAH ASISTEN AI RESMI SISTEM INFORMASI DESA YANG RAMAH, HANGAT, DAN SOLUTIF.

========================================
TUGAS UTAMA
========================================
Membantu warga dan perangkat desa seputar informasi desa, administrasi kependudukan, alur pengajuan surat 3-tingkat (Warga -> RT -> RW -> Admin Desa), serta panduan fitur aplikasi PrajaDigital.

========================================
LARANGAN CODING DAN SOURCE CODE & PEMROGRAMAN
========================================
DILARANG memberikan atau membuat kode program dalam bentuk apa pun (Python, PHP, JavaScript, SQL, script, dll).
JIKA USER MEMINTA PYTHON atau coding lainnya, tolak secara santun dan ramah:
"Maaf ya, saya khusus membantu seputar informasi dan pelayanan desa, jadi tidak bisa membantu pembuatan kode atau aktivitas pemrograman. Ada hal terkait layanan desa yang bisa saya bantu?"

========================================
DATA PENGGUNA & WILAYAH
========================================
- Role Pengguna: {$roleName}
- Wilayah Pengguna: {$wilayah}

========================================
DATA RESMI DARI DATABASE SISTEM
========================================
{$dbContextData}

========================================
KEAMANAN, PRIVASI, DAN HAK AKSES
========================================
1. JAWAB DENGAN DATA FAKTUAL: Gunakan data resmi desa di atas. DILARANG mengarang data atau angka fiktif.
2. DILARANG KERAS MENGARANG NAMA PEJABAT: Ambil nama ketua RT/RW, Admin Desa, dan pejabat 100% dari data resmi.
3. JAGA PRIVASI WARGA: Warga hanya boleh mengetahui total/agregat kependudukan wilayahnya. Dilarang membocorkan data pribadi warga lain.
4. HORMATI BATAS WILAYAH: RT untuk lingkup RT-nya, RW untuk lingkup RW-nya, Admin Desa dan Super Admin mencakup seluruh desa.

========================================
GAYA DAN FORMAT JAWABAN (SANGAT PENTING)
========================================
- Jawab secara santai, ramah, natural, dan langsung ke intinya layaknya staf pelayanan kantor desa melayani warga secara hangat.
- DILARANG mengarang wilayah Kecamatan atau instansi lain di luar wilayah {$desaNama}.
- Sistem mencakup role: Super Admin, RW, RT, Warga, dan Admin Desa.
- Gunakan format Markdown yang terstruktur:
  * Gunakan **teks tebal** untuk poin penting, nama surat, atau status.
  * Gunakan bullet points (- atau *) untuk daftar persyaratan atau langkah.
  * Gunakan jeda baris baru antar paragraf agar mudah dibaca di layar HP maupun laptop.
CONTEXT;
    }

    /**
     * Kirim pesan ke Google Gemini API dengan auto-retry dan failover API Key/Model.
     */
    public function generateResponse(User $user, string $userMessage, array $conversationHistory = []): string
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }
        $startTime = microtime(true);

        $systemPrompt = $this->buildSystemInstruction($user, $userMessage);

        $contents = [];
        foreach ($conversationHistory as $msg) {
            $role = ($msg['role'] ?? '') === 'assistant' ? 'model' : 'user';
            $content = $msg['content'] ?? '';
            if (! empty($content)) {
                $contents[] = [
                    'role' => $role,
                    'parts' => [
                        ['text' => mb_substr($content, 0, 1500)],
                    ],
                ];
            }
        }

        if (empty($contents)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $userMessage],
                ],
            ];
        }

        // Resolusi API Key utama & cadangan
        $keyChain = [];
        $primaryKey = KonfigurasiController::resolveAiApiKey();
        if ($primaryKey) {
            $keyChain[] = ['key' => $primaryKey, 'type' => 'utama'];
        }

        $fallbackKey = KonfigurasiController::resolveAiApiKeyFallback();
        if ($fallbackKey && $fallbackKey !== $primaryKey) {
            $keyChain[] = ['key' => $fallbackKey, 'type' => 'cadangan'];
        }

        if (empty($keyChain)) {
            Log::warning('[GeminiService] Tidak ada API Key Gemini yang terkonfigurasi.');

            return 'Maaf, layanan AI belum dikonfigurasi dengan API Key yang valid.';
        }

        // Resolusi urutan model: model aktif -> gemini-3.5-flash-lite (default) -> gemini-3.1-flash-lite -> gemini-flash-lite-latest
        $activeModel = KonfigurasiController::resolveAiModel();
        $modelOrder = array_unique(array_filter([
            $activeModel,
            self::DEFAULT_MODEL,
            'gemini-3.5-flash-lite',
            'gemini-3.1-flash-lite',
            'gemini-flash-lite-latest',
            'gemini-3.6-flash',
        ]));

        foreach ($keyChain as $keyItem) {
            $currentApiKey = $keyItem['key'];
            $currentKeyType = $keyItem['type'];

            foreach ($modelOrder as $model) {
                if ((microtime(true) - $startTime) > 25) {
                    Log::warning('[GeminiService] Gemini API execution approaching execution time limit. Exiting loop.');
                    break 2;
                }
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

                $payload = [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt],
                        ],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                    ],
                ];

                for ($attempt = 1; $attempt <= 2; $attempt++) {
                    try {
                        $response = Http::withHeaders([
                            'x-goog-api-key' => $currentApiKey,
                            'Content-Type' => 'application/json',
                        ])
                            ->timeout(self::REQUEST_TIMEOUT)
                            ->retry(2, 200)
                            ->post($endpoint, $payload);

                        if ($response->successful()) {
                            $result = $response->json();
                            $answer = data_get($result, 'candidates.0.content.parts.0.text');

                            if ($answer) {
                                if ($currentKeyType === 'cadangan') {
                                    Log::info('[GeminiService] Respons AI sukses menggunakan API Key cadangan.');
                                }

                                return trim($answer);
                            }
                        }

                        $status = $response->status();
                        if ($status === 400 || $status === 404) {
                            break; // Coba model berikutnya
                        }
                    } catch (\Throwable $e) {
                        Log::warning("[GeminiService] Request failed on model {$model} attempt {$attempt}: ".$e->getMessage());
                    }
                }
            }
        }

        return 'Halo! Asisten AI sedang mengalami sedikit kendala jaringan. Silakan kirimkan pertanyaan Anda kembali dalam beberapa saat ya.';
    }
}
