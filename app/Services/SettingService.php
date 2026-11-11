<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SettingService
{
    private const CACHE_PREFIX = 'prajadigital.setting.';

    private const CACHE_TTL = 315360000; // 10 tahun (persistent cache)

    /**
     * Ambil setting dari Cache -> Database -> Fallback Default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        // 1. Cek Cache
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return $cached !== null ? $cached : $default;
        }

        // 2. Cek Database
        try {
            $record = SystemSetting::where('key', $key)->first();

            if ($record !== null && $record->value !== null && $record->value !== '') {
                $value = $record->value;

                if ($record->is_encrypted) {
                    try {
                        $value = Crypt::decryptString($value);
                    } catch (\Throwable $e) {
                        Log::error("[SettingService] Gagal mendekripsi setting: {$key}", ['error' => $e->getMessage()]);
                        $value = $default;
                    }
                }

                Cache::forever($cacheKey, $value);

                return $value;
            }
        } catch (\Throwable $e) {
            Log::warning("[SettingService] Error query database untuk key {$key}", ['error' => $e->getMessage()]);
        }

        // 3. Fallback ke nilai default (.env / config)
        return $default;
    }

    /**
     * Simpan / Perbarui setting di Database dan Cache secara langsung (Live Update).
     *
     * @throws \Throwable jika gagal menulis ke database.
     */
    public static function set(string $key, mixed $value, bool $encrypt = false, string $group = 'general', ?string $description = null): void
    {
        $cacheKey = self::CACHE_PREFIX.$key;
        $plainValue = $value !== null ? (string) $value : null;

        $dbValue = $plainValue;
        if ($encrypt && $plainValue !== null && $plainValue !== '') {
            try {
                $dbValue = Crypt::encryptString($plainValue);
            } catch (\Throwable $e) {
                Log::error("[SettingService] Gagal mengenkripsi setting: {$key}", ['error' => $e->getMessage()]);
                throw $e;
            }
        }

        try {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $dbValue,
                    'is_encrypted' => $encrypt,
                    'group' => $group,
                    'description' => $description,
                ]
            );
        } catch (\Throwable $e) {
            Log::error("[SettingService] Gagal menyimpan setting ke database: {$key}", ['error' => $e->getMessage()]);
            throw $e;
        }

        // Hapus cache lama terlebih dahulu untuk mencegah stale-cache,
        // terutama saat menggunakan database cache store.
        Cache::forget($cacheKey);
        Cache::forever($cacheKey, $plainValue);
    }

    /**
     * Hapus setting dari Database dan Cache.
     */
    public static function forget(string $key): void
    {
        $cacheKey = self::CACHE_PREFIX.$key;

        try {
            SystemSetting::where('key', $key)->delete();
        } catch (\Throwable $e) {
            Log::warning("[SettingService] Gagal menghapus key {$key} dari DB", ['error' => $e->getMessage()]);
        }

        Cache::forget($cacheKey);
    }

    /**
     * Cek apakah key memiliki record di database.
     */
    public static function hasInDatabase(string $key): bool
    {
        try {
            return SystemSetting::where('key', $key)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Masking string rahasia (API key/Token) untuk tampilan UI yang aman.
     */
    public static function maskSecret(?string $secret, int $visibleStart = 4, int $visibleEnd = 4): string
    {
        if (empty($secret) || ! is_string($secret)) {
            return 'Belum dikonfigurasi';
        }

        $length = strlen($secret);
        if ($length <= ($visibleStart + $visibleEnd)) {
            return '****'.substr($secret, -2);
        }

        $maskLength = max(4, $length - ($visibleStart + $visibleEnd));

        return substr($secret, 0, $visibleStart).str_repeat('*', $maskLength).substr($secret, -$visibleEnd);
    }
}
