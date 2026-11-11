<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KonfigurasiController extends Controller
{
    public const SETTING_AI_KEY = 'ai_api_key';

    public const SETTING_AI_KEY_FALLBACK = 'ai_api_key_fallback';

    public const SETTING_AI_MODEL = 'ai_model';

    public const SETTING_WA_TOKEN = 'wa_token';

    public const SETTING_WA_SIMULATION = 'wa_simulation';

    public static function resolveAiApiKey(): ?string
    {
        return SettingService::get(
            self::SETTING_AI_KEY,
            config('services.gemini.api_key')
        );
    }

    /**
     * Baca API Key cadangan aktif: Database/Cache > config('services.gemini.api_key_fallback').
     * Tidak pernah dikembalikan ke browser dalam bentuk lengkap.
     */
    public static function resolveAiFallbackApiKey(): ?string
    {
        return SettingService::get(
            self::SETTING_AI_KEY_FALLBACK,
            config('services.gemini.api_key_fallback')
        );
    }

    /**
     * Daftar 5 model Gemini 3.x resmi, gratis & aktif yang didukung di dropdown Konfigurasi AI.
     * Model kustom (opsi 'custom') tetap didukung melalui custom_model_engine.
     */
    public const ALLOWED_AI_MODELS = [
        'gemini-3.5-flash-lite',
        'gemini-3.1-flash-lite',
        'gemini-flash-lite-latest',
        'gemini-3.6-flash',
        'gemini-3.8-flash',
    ];

    public static function resolveAiModel(): string
    {
        return (string) SettingService::get(
            self::SETTING_AI_MODEL,
            config('services.gemini.model', 'gemini-3.5-flash-lite')
        );
    }

    public static function resolveWaToken(): ?string
    {
        return SettingService::get(
            self::SETTING_WA_TOKEN,
            config('services.whatsapp.token')
        );
    }

    public static function resolveWaSimulation(): bool
    {
        $setting = SettingService::get(self::SETTING_WA_SIMULATION, null);
        if ($setting !== null) {
            return (bool) $setting;
        }

        return (bool) config('services.whatsapp.simulation', true);
    }

    public function index(): View
    {
        $rawApiKey = self::resolveAiApiKey();
        $isAiConfigured = ! empty($rawApiKey);
        $maskedApiKey = SettingService::maskSecret($rawApiKey);

        $rawFallbackKey = self::resolveAiFallbackApiKey();
        $isFallbackConfigured = ! empty($rawFallbackKey);
        $maskedFallbackKey = SettingService::maskSecret($rawFallbackKey);

        $isSuperAdmin = auth()->check() && auth()->user()->hasRole('super_admin');

        $aiConfig = [
            'provider' => config('services.gemini.provider', 'gemini'),
            'model' => self::resolveAiModel(),
            'api_key_masked' => $maskedApiKey,
            'api_key_fallback_masked' => $maskedFallbackKey,
            'api_key_raw' => $isSuperAdmin ? $rawApiKey : null,
            'api_key_fallback_raw' => $isSuperAdmin ? $rawFallbackKey : null,
            'is_configured' => $isAiConfigured,
            'is_fallback_configured' => $isFallbackConfigured,
            'source_primary' => SettingService::hasInDatabase(self::SETTING_AI_KEY) ? 'database' : (config('services.gemini.api_key') ? 'env' : 'none'),
            'source_fallback' => SettingService::hasInDatabase(self::SETTING_AI_KEY_FALLBACK) ? 'database' : (config('services.gemini.api_key_fallback') ? 'env' : 'none'),
            'using_override' => SettingService::hasInDatabase(self::SETTING_AI_KEY),
            'fallback_using_override' => SettingService::hasInDatabase(self::SETTING_AI_KEY_FALLBACK),
        ];

        $waToken = self::resolveWaToken();
        $maskedWaToken = SettingService::maskSecret($waToken);
        $isWaSimulation = self::resolveWaSimulation() || empty($waToken);

        $waConfig = [
            'provider' => config('services.whatsapp.provider', 'fonnte'),
            'endpoint' => config('services.whatsapp.endpoint', 'https://api.fonnte.com/send'),
            'token_masked' => $maskedWaToken,
            'token_raw' => $isSuperAdmin ? $waToken : null,
            'is_configured' => ! empty($waToken),
            'is_simulation' => $isWaSimulation,
            'source_token' => SettingService::hasInDatabase(self::SETTING_WA_TOKEN) ? 'database' : (config('services.whatsapp.token') ? 'env' : 'none'),
            'using_override' => SettingService::hasInDatabase(self::SETTING_WA_TOKEN),
        ];

        $systemInfo = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'timezone' => config('app.timezone'),
        ];

        $recentConfigLogs = AuditLog::where('model_type', 'SystemConfig')
            ->latest()
            ->take(5)
            ->get();

        return view('konfigurasi.index', compact(
            'aiConfig',
            'waConfig',
            'systemInfo',
            'recentConfigLogs'
        ));
    }

    /**
     * Perbarui konfigurasi AI (hanya Super Admin).
     * Input kosong mempertahankan nilai lama.
     */
    public function updateAi(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $allowedInList = implode(',', self::ALLOWED_AI_MODELS).',custom';

        $validated = $request->validate([
            'ai_api_key' => 'nullable|string|max:512',
            'ai_api_key_fallback' => 'nullable|string|max:512',
            'ai_model' => ['nullable', 'string', 'max:100', 'in:'.$allowedInList],
            'model_engine' => ['nullable', 'string', 'max:100', 'in:'.$allowedInList],
            'custom_model_engine' => ['nullable', 'string', 'max:100', 'regex:/^gemini-[\w.\-]+$/'],
        ], [
            'ai_api_key.max' => 'API Key Utama terlalu panjang (maks 512 karakter).',
            'ai_api_key_fallback.max' => 'API Key Cadangan terlalu panjang (maks 512 karakter).',
            'ai_model.in' => 'Pilihan model tidak valid.',
            'model_engine.in' => 'Pilihan model tidak valid.',
            'ai_model.max' => 'Nama model terlalu panjang (maks 100 karakter).',
            'model_engine.max' => 'Nama model terlalu panjang (maks 100 karakter).',
            'custom_model_engine.regex' => 'ID model kustom tidak valid. Format yang diterima: gemini-nama-model.',
            'custom_model_engine.max' => 'ID model kustom terlalu panjang (maks 100 karakter).',
        ]);

        $newKey = trim($validated['ai_api_key'] ?? '');
        $newFallbackKey = trim($validated['ai_api_key_fallback'] ?? '');
        $modelInput = trim($validated['model_engine'] ?? $validated['ai_model'] ?? '');
        $customModel = trim($validated['custom_model_engine'] ?? '');

        // Jika user memilih opsi "custom", gunakan nilai dari field kustom
        if ($modelInput === 'custom' && $customModel !== '') {
            $newModel = $customModel;
        } elseif ($modelInput !== '' && $modelInput !== 'custom') {
            $newModel = $modelInput;
        } else {
            $newModel = '';
        }
        $changed = [];

        try {
            if ($newKey !== '') {
                SettingService::set(self::SETTING_AI_KEY, $newKey, encrypt: true, group: 'ai', description: 'API Key Gemini Utama');
                config(['services.gemini.api_key' => $newKey]);
                $changed[] = 'API Key Utama';
            }

            if ($newFallbackKey !== '') {
                SettingService::set(self::SETTING_AI_KEY_FALLBACK, $newFallbackKey, encrypt: true, group: 'ai', description: 'API Key Gemini Cadangan (Fallback)');
                config(['services.gemini.api_key_fallback' => $newFallbackKey]);
                $changed[] = 'API Key Cadangan';
            }

            if ($newModel !== '') {
                $currentModel = self::resolveAiModel();
                $dbExists = SettingService::hasInDatabase(self::SETTING_AI_MODEL);

                if ($newModel !== $currentModel || ! $dbExists) {
                    SettingService::set(self::SETTING_AI_MODEL, $newModel, encrypt: false, group: 'ai', description: 'Model Engine Gemini');
                    config(['services.gemini.model' => $newModel]);
                    $changed[] = 'model ('.$newModel.')';
                } elseif ($newKey !== '' || $newFallbackKey !== '') {
                    SettingService::set(self::SETTING_AI_MODEL, $newModel, encrypt: false, group: 'ai', description: 'Model Engine Gemini');
                }
            }
        } catch (\Throwable $e) {
            Log::error('[KonfigurasiController] Gagal menyimpan konfigurasi AI', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->to(route('konfigurasi.index', [], false))
                ->with('error', 'Gagal menyimpan konfigurasi AI ke database. Periksa log server untuk detail error: '.$e->getMessage());
        }

        if (empty($changed)) {
            return redirect()->to(route('konfigurasi.index', [], false))
                ->with('info', 'Tidak ada perubahan — konfigurasi lama dipertahankan.');
        }

        $this->writeAuditLog('Konfigurasi AI diperbarui: '.implode(', ', $changed), $request);

        return redirect()->to(route('konfigurasi.index', [], false))
            ->with('success', 'Konfigurasi AI berhasil diperbarui: '.implode(', ', $changed).'.');
    }

    /**
     * Perbarui konfigurasi WhatsApp Fonnte (hanya Super Admin).
     * Input kosong mempertahankan nilai lama.
     */
    public function updateWhatsapp(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'wa_token' => 'nullable|string|max:512',
            'wa_simulation' => 'nullable|string|in:0,1',
        ], [
            'wa_token.max' => 'Token terlalu panjang (maks 512 karakter).',
        ]);

        $newToken = trim($validated['wa_token'] ?? '');
        $simulationRaw = $validated['wa_simulation'] ?? null;
        $changed = [];

        try {
            if ($newToken !== '') {
                SettingService::set(self::SETTING_WA_TOKEN, $newToken, encrypt: true, group: 'whatsapp', description: 'Token WhatsApp Fonnte');
                config(['services.whatsapp.token' => $newToken]);
                $changed[] = 'token';
            }

            if ($simulationRaw !== null) {
                $simValue = (bool) (int) $simulationRaw;
                $currentSim = self::resolveWaSimulation();
                $dbExists = SettingService::hasInDatabase(self::SETTING_WA_SIMULATION);

                if ($simValue !== $currentSim || ! $dbExists) {
                    SettingService::set(self::SETTING_WA_SIMULATION, $simValue, encrypt: false, group: 'whatsapp', description: 'Mode Simulasi WhatsApp');
                    config(['services.whatsapp.simulation' => $simValue]);
                    $changed[] = 'mode simulasi ('.($simValue ? 'aktif' : 'nonaktif').')';
                } elseif ($newToken !== '') {
                    SettingService::set(self::SETTING_WA_SIMULATION, $simValue, encrypt: false, group: 'whatsapp', description: 'Mode Simulasi WhatsApp');
                }
            }
        } catch (\Throwable $e) {
            Log::error('[KonfigurasiController] Gagal menyimpan konfigurasi WhatsApp', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->to(route('konfigurasi.index', [], false))
                ->with('error', 'Gagal menyimpan konfigurasi WhatsApp ke database. Periksa log server untuk detail error: '.$e->getMessage());
        }

        if (empty($changed)) {
            return redirect()->to(route('konfigurasi.index', [], false))
                ->with('info', 'Tidak ada perubahan — input kosong mempertahankan konfigurasi lama.');
        }

        $this->writeAuditLog('Konfigurasi WhatsApp diperbarui: '.implode(', ', $changed), $request);

        return redirect()->to(route('konfigurasi.index', [], false))
            ->with('success', 'Konfigurasi WhatsApp berhasil diperbarui: '.implode(', ', $changed).'.');
    }

    /**
     * Probe API Key Gemini tunggal tanpa mengekspos isi key.
     *
     * @return array{success: bool, status: int, message: string, sample?: string}
     */
    private function probeGeminiApiKey(string $apiKey, string $model): array
    {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent';

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post($endpoint, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'Halo, apakah kamu aktif? Balas singkat.']]],
                ],
                'generationConfig' => ['maxOutputTokens' => 32],
            ]);

            if ($response->successful()) {
                $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '(respon kosong)');

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => 'Koneksi ke Gemini AI berhasil.',
                    'sample' => mb_substr(trim($text), 0, 120),
                ];
            }

            $status = $response->status();
            $message = match (true) {
                $status === 400 => 'Request tidak valid (HTTP 400). Pastikan nama model benar.',
                $status === 401 || $status === 403 => 'API Key tidak valid atau otorisasi ditolak (HTTP '.$status.').',
                $status === 404 => 'Model "'.$model.'" tidak ditemukan (HTTP 404).',
                $status === 429 => 'Batas kuota/rate limit tercapai (HTTP 429).',
                $status >= 500 => 'Server Gemini sedang gangguan (HTTP '.$status.').',
                default => 'Gagal terhubung ke Gemini (HTTP '.$status.').',
            };

            return [
                'success' => false,
                'status' => $status,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 0,
                'message' => 'Koneksi gagal: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Uji koneksi ke Gemini AI — probe minimal, mendukung pengujian key utama dan cadangan.
     */
    public function testAi(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin();

        $target = $request->input('target', 'auto');
        $model = self::resolveAiModel();
        $primaryKey = self::resolveAiApiKey();
        $fallbackKey = self::resolveAiFallbackApiKey();

        if ($target === 'fallback') {
            if (! $fallbackKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Key cadangan belum dikonfigurasi.',
                ], 422);
            }

            $probe = $this->probeGeminiApiKey($fallbackKey, $model);
            $probe['model'] = $model;
            $probe['target'] = 'fallback';

            return response()->json($probe, $probe['success'] ? 200 : 422);
        }

        if ($target === 'primary') {
            if (! $primaryKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Key utama belum dikonfigurasi.',
                ], 422);
            }

            $probe = $this->probeGeminiApiKey($primaryKey, $model);
            $probe['model'] = $model;
            $probe['target'] = 'primary';

            return response()->json($probe, $probe['success'] ? 200 : 422);
        }

        // Mode 'auto': Cek utama terlebih dahulu, lalu fallback jika utama bermasalah
        if (! $primaryKey && ! $fallbackKey) {
            return response()->json([
                'success' => false,
                'message' => 'API Key AI belum dikonfigurasi. Simpan terlebih dahulu sebelum menguji.',
            ], 422);
        }

        $primaryResult = $primaryKey ? $this->probeGeminiApiKey($primaryKey, $model) : null;

        if ($primaryResult && $primaryResult['success']) {
            $extraNote = $fallbackKey ? ' (API Key Cadangan siap sebagai backup)' : '';

            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke Gemini AI berhasil.'.$extraNote,
                'model' => $model,
                'sample' => $primaryResult['sample'] ?? null,
                'fallback_configured' => (bool) $fallbackKey,
                'active_key' => 'primary',
            ]);
        }

        // Jika API Key utama gagal atau belum diset, uji API Key cadangan
        if ($fallbackKey) {
            $fallbackResult = $this->probeGeminiApiKey($fallbackKey, $model);

            if ($fallbackResult['success']) {
                $primaryReason = $primaryResult ? $primaryResult['message'] : 'API Key Utama belum diset';

                return response()->json([
                    'success' => true,
                    'message' => 'API Key Utama bermasalah ('.$primaryReason.'), namun pengalihan otomatis (fallback) ke API Key Cadangan BERHASIL.',
                    'model' => $model,
                    'sample' => $fallbackResult['sample'] ?? null,
                    'fallback_used' => true,
                    'active_key' => 'fallback',
                ]);
            }

            $primaryReason = $primaryResult ? $primaryResult['message'] : 'API Key Utama belum diset';

            return response()->json([
                'success' => false,
                'message' => 'API Key Utama dan Cadangan keduanya gagal. Utama: '.$primaryReason.' | Cadangan: '.$fallbackResult['message'],
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => $primaryResult['message'] ?? 'Gagal terhubung ke Gemini.',
        ], 422);
    }

    /**
     * Uji koneksi ke Fonnte — hanya cek device info, TIDAK kirim pesan WA sungguhan.
     */
    public function testWa(Request $request): JsonResponse
    {
        $this->authorizeSuperAdmin();

        $token = self::resolveWaToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token WhatsApp belum dikonfigurasi. Simpan terlebih dahulu sebelum menguji.',
            ], 422);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post('https://api.fonnte.com/device', []);

            $json = $response->json();

            if ($response->successful() && isset($json['status']) && $json['status']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi ke Fonnte berhasil. Device terdaftar.',
                    'device' => $json['name'] ?? ($json['target'] ?? 'unknown'),
                ]);
            }

            $reason = $json['reason'] ?? ($json['message'] ?? 'Token tidak valid atau device offline.');

            return response()->json(['success' => false, 'message' => $reason], 422);
        } catch (\Throwable $e) {
            Log::error('[KonfigurasiController] Test WA exception', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke Fonnte gagal: '.$e->getMessage(),
            ], 503);
        }
    }

    private function authorizeSuperAdmin(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('super_admin')) {
            Log::warning('[KonfigurasiController] Akses ditolak', [
                'user_id' => $user?->id,
                'role' => $user?->role?->name,
                'ip' => request()->ip(),
            ]);

            abort(403, 'Hanya Super Admin yang dapat mengubah konfigurasi sistem.');
        }
    }

    private function writeAuditLog(string $activity, Request $request): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'activity' => $activity,
                'model_type' => 'SystemConfig',
                'model_id' => null,
                'old_value' => null,
                'new_value' => null,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[KonfigurasiController] Gagal menulis audit log', ['error' => $e->getMessage()]);
        }
    }
}
