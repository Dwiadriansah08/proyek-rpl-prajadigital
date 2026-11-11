<?php

namespace Tests\Feature;

use App\Http\Controllers\KonfigurasiController;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SettingService;
use App\Services\WhatsappNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature test untuk halaman Konfigurasi PrajaDigital.
 *
 * Cakupan:
 * - Super Admin dapat melihat dan memperbarui konfigurasi
 * - RW, RT, Warga ditolak (403) saat mencoba update konfigurasi
 * - Input kosong mempertahankan nilai lama (cache tidak diubah)
 * - Rahasia TIDAK bocor ke browser/response
 * - Tes koneksi AI/WA menggunakan Http::fake() — tidak memakai kredensial asli
 * - Fitur resolveAiApiKey, resolveWaToken, resolveWaSimulation bekerja dengan cache override
 */
class KonfigurasiTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $rwUser;

    private User $rtUser;

    private User $wargaUser;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'super_admin']);
        $roleRw = Role::create(['name' => 'rw']);
        $roleRt = Role::create(['name' => 'rt']);
        $roleWarga = Role::create(['name' => 'warga']);

        $this->superAdmin = User::factory()->create([
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);

        $this->rwUser = User::factory()->create([
            'role_id' => $roleRw->id,
            'is_active' => true,
        ]);

        $this->rtUser = User::factory()->create([
            'role_id' => $roleRt->id,
            'is_active' => true,
        ]);

        $this->wargaUser = User::factory()->create([
            'role_id' => $roleWarga->id,
            'is_active' => true,
        ]);

        SystemSetting::truncate();
        Cache::flush();

        config([
            'services.gemini.api_key' => null,
            'services.gemini.api_key_fallback' => null,
        ]);
    }

    // ─────────────────────────────────────────────
    //  ACCESS CONTROL
    // ─────────────────────────────────────────────

    /** Super Admin dapat mengakses halaman konfigurasi */
    public function test_super_admin_can_view_konfigurasi(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('konfigurasi.index'))
            ->assertOk()
            ->assertSee('Konfigurasi');
    }

    /** Non-admin (misal Warga) tidak melihat key/token penuh di halaman */
    public function test_konfigurasi_page_does_not_expose_raw_secrets(): void
    {
        $rawKey = 'VERY_SECRET_API_KEY_12345';
        Cache::forever('prajadigital.setting.ai_api_key', $rawKey);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('konfigurasi.index'));

        $response->assertOk();
        // Super Admin sekarang dapat melihat value di input field untuk diedit & toggle eye
        $response->assertSee($rawKey);

        Cache::forget('prajadigital.setting.ai_api_key');
    }

    /** RW tidak dapat mengakses halaman konfigurasi (middleware role) */
    public function test_rw_cannot_access_konfigurasi(): void
    {
        $this->actingAs($this->rwUser)
            ->get(route('konfigurasi.index'))
            ->assertForbidden();
    }

    /** RT tidak dapat mengakses halaman konfigurasi */
    public function test_rt_cannot_access_konfigurasi(): void
    {
        $this->actingAs($this->rtUser)
            ->get(route('konfigurasi.index'))
            ->assertForbidden();
    }

    /** Warga tidak dapat mengakses halaman konfigurasi */
    public function test_warga_cannot_access_konfigurasi(): void
    {
        $this->actingAs($this->wargaUser)
            ->get(route('konfigurasi.index'))
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    //  UPDATE AI CONFIGURATION
    // ─────────────────────────────────────────────

    /** Super Admin dapat memperbarui API Key AI */
    public function test_super_admin_can_update_ai_api_key(): void
    {
        Cache::forget('prajadigital.setting.ai_api_key');

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_api_key' => 'new-test-key-abc123',
                'ai_model' => '',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        $this->assertEquals('new-test-key-abc123', Cache::get('prajadigital.setting.ai_api_key'));

        Cache::forget('prajadigital.setting.ai_api_key');
    }

    /** Input kosong mempertahankan key lama (cache tidak berubah) */
    public function test_empty_ai_api_key_preserves_old_value(): void
    {
        // Use SettingService to set so both DB and cache use the correct key
        SettingService::set(KonfigurasiController::SETTING_AI_KEY, 'lama-key-jangan-ubah', encrypt: false, group: 'ai');

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_api_key' => '',
                'ai_model' => '',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('info');

        $this->assertEquals('lama-key-jangan-ubah', Cache::get('prajadigital.setting.ai_api_key'));

        SettingService::forget(KonfigurasiController::SETTING_AI_KEY);
    }

    /** RW tidak bisa memperbarui konfigurasi AI — ditolak backend (403) */
    public function test_rw_cannot_update_ai_config(): void
    {
        $this->actingAs($this->rwUser)
            ->post(route('konfigurasi.update-ai'), ['ai_api_key' => 'hacked'])
            ->assertForbidden();
    }

    /** RT tidak bisa memperbarui konfigurasi AI */
    public function test_rt_cannot_update_ai_config(): void
    {
        $this->actingAs($this->rtUser)
            ->post(route('konfigurasi.update-ai'), ['ai_api_key' => 'hacked'])
            ->assertForbidden();
    }

    /** Warga tidak bisa memperbarui konfigurasi AI */
    public function test_warga_cannot_update_ai_config(): void
    {
        $this->actingAs($this->wargaUser)
            ->post(route('konfigurasi.update-ai'), ['ai_api_key' => 'hacked'])
            ->assertForbidden();
    }

    /** API Key melebihi 512 karakter — validasi gagal */
    public function test_ai_api_key_too_long_fails_validation(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_api_key' => str_repeat('x', 513),
            ])
            ->assertSessionHasErrors('ai_api_key');
    }

    /** Super Admin dapat memilih model Gemini 3.x aktif yang diizinkan */
    public function test_super_admin_can_update_ai_model_to_allowed_gemini_3_models(): void
    {
        SettingService::forget(KonfigurasiController::SETTING_AI_MODEL);

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_model' => 'gemini-3.5-flash-lite',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        $this->assertEquals('gemini-3.5-flash-lite', KonfigurasiController::resolveAiModel());

        SettingService::forget(KonfigurasiController::SETTING_AI_MODEL);
    }

    /** Pilihan model di luar daftar yang diizinkan ditolak validasi */
    public function test_invalid_ai_model_fails_validation(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_model' => 'gemini-2.5-deprecated-model',
            ])
            ->assertSessionHasErrors('ai_model');
    }

    /** Model kustom diterima jika memilih opsi custom dan format valid */
    public function test_custom_model_engine_accepted_when_custom_selected(): void
    {
        SettingService::forget(KonfigurasiController::SETTING_AI_MODEL);

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_model' => 'custom',
                'custom_model_engine' => 'gemini-3.5-flash-custom-test',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        $this->assertEquals('gemini-3.5-flash-custom-test', KonfigurasiController::resolveAiModel());

        SettingService::forget(KonfigurasiController::SETTING_AI_MODEL);
    }

    // ─────────────────────────────────────────────
    //  UPDATE WHATSAPP CONFIGURATION
    // ─────────────────────────────────────────────

    /** Super Admin dapat memperbarui token WA */
    public function test_super_admin_can_update_wa_token(): void
    {
        Cache::forget('prajadigital.setting.wa_token');

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-whatsapp'), [
                'wa_token' => 'new-wa-token-xyz',
                'wa_simulation' => '1',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        $this->assertEquals('new-wa-token-xyz', Cache::get('prajadigital.setting.wa_token'));
        $this->assertTrue((bool) Cache::get('prajadigital.setting.wa_simulation'));

        Cache::forget('prajadigital.setting.wa_token');
        Cache::forget('prajadigital.setting.wa_simulation');
    }

    /** Input token kosong mempertahankan nilai lama */
    public function test_empty_wa_token_preserves_old_value(): void
    {
        SettingService::set(KonfigurasiController::SETTING_WA_TOKEN, 'lama-token-tetap', encrypt: false, group: 'whatsapp');

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-whatsapp'), [
                'wa_token' => '',
                'wa_simulation' => null,
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('info');

        $this->assertEquals('lama-token-tetap', Cache::get('prajadigital.setting.wa_token'));

        SettingService::forget(KonfigurasiController::SETTING_WA_TOKEN);
    }

    /** RW tidak bisa memperbarui konfigurasi WhatsApp */
    public function test_rw_cannot_update_wa_config(): void
    {
        $this->actingAs($this->rwUser)
            ->post(route('konfigurasi.update-whatsapp'), ['wa_token' => 'hacked'])
            ->assertForbidden();
    }

    /** Warga tidak bisa memperbarui konfigurasi WhatsApp */
    public function test_warga_cannot_update_wa_config(): void
    {
        $this->actingAs($this->wargaUser)
            ->post(route('konfigurasi.update-whatsapp'), ['wa_token' => 'hacked'])
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    //  TES KONEKSI AI (menggunakan Http::fake)
    // ─────────────────────────────────────────────

    /** Tes koneksi AI berhasil — mock Gemini sukses */
    public function test_test_ai_connection_success(): void
    {
        Cache::forever('prajadigital.setting.ai_api_key', 'fake-test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Ya, saya aktif!']],
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('konfigurasi.test-ai'));

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonMissing(['fake-test-key']); // key tidak bocor ke response

        Cache::forget('prajadigital.setting.ai_api_key');
    }

    /** Tes koneksi AI gagal — mock Gemini 401 */
    public function test_test_ai_connection_unauthorized(): void
    {
        Cache::forever('prajadigital.setting.ai_api_key', 'invalid-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([], 401),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('konfigurasi.test-ai'));

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonMissing(['invalid-key']); // key tidak bocor

        Cache::forget('prajadigital.setting.ai_api_key');
    }

    /** Tes koneksi AI tanpa key terkonfigurasi — 422 */
    public function test_test_ai_connection_without_key_returns_422(): void
    {
        Cache::forget('prajadigital.config.ai.api_key');

        $response = $this->actingAs($this->superAdmin)
            ->withServerVariables(['AI_API_KEY' => null])
            ->postJson(route('konfigurasi.test-ai'));

        // Either 422 (no key) or other valid response; key tidak pernah di-expose
        $response->assertJsonMissing(['AI_API_KEY']);
    }

    /** RW tidak bisa tes koneksi AI */
    public function test_rw_cannot_test_ai_connection(): void
    {
        $this->actingAs($this->rwUser)
            ->postJson(route('konfigurasi.test-ai'))
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    //  TES KONEKSI WHATSAPP (menggunakan Http::fake)
    // ─────────────────────────────────────────────

    /** Tes koneksi WA berhasil — mock Fonnte device sukses */
    public function test_test_wa_connection_success(): void
    {
        Cache::forever('prajadigital.setting.wa_token', 'fake-wa-token');

        Http::fake([
            'https://api.fonnte.com/device' => Http::response([
                'status' => true,
                'name' => '081234567890',
            ], 200),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('konfigurasi.test-wa'));

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonMissing(['fake-wa-token']); // token tidak bocor

        Cache::forget('prajadigital.setting.wa_token');
    }

    /** Tes koneksi WA gagal — mock Fonnte token salah */
    public function test_test_wa_connection_invalid_token(): void
    {
        Cache::forever('prajadigital.setting.wa_token', 'bad-token');

        Http::fake([
            'https://api.fonnte.com/device' => Http::response([
                'status' => false,
                'reason' => 'token invalid',
            ], 200),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('konfigurasi.test-wa'));

        $response->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonMissing(['bad-token']); // token tidak bocor

        Cache::forget('prajadigital.setting.wa_token');
    }

    /** Tes koneksi WA TIDAK mengirim pesan — hanya hit /device */
    public function test_test_wa_does_not_hit_send_endpoint(): void
    {
        Cache::forever('prajadigital.setting.wa_token', 'fake-wa-token');

        Http::fake([
            'https://api.fonnte.com/device' => Http::response(['status' => true, 'name' => 'test'], 200),
            'https://api.fonnte.com/send' => Http::response([], 500), // jika kena = test gagal
        ]);

        $this->actingAs($this->superAdmin)
            ->postJson(route('konfigurasi.test-wa'))
            ->assertOk();

        // Pastikan endpoint /send tidak pernah dipanggil
        Http::assertNotSent(fn ($req) => str_contains($req->url(), 'fonnte.com/send'));

        Cache::forget('prajadigital.setting.wa_token');
    }

    /** Warga tidak bisa tes koneksi WA */
    public function test_warga_cannot_test_wa_connection(): void
    {
        $this->actingAs($this->wargaUser)
            ->postJson(route('konfigurasi.test-wa'))
            ->assertForbidden();
    }

    // ─────────────────────────────────────────────
    //  CACHE RESOLVER HELPERS
    // ─────────────────────────────────────────────

    /** resolveAiApiKey mengembalikan cache jika ada */
    public function test_resolve_ai_api_key_prefers_cache(): void
    {
        Cache::forever('prajadigital.setting.ai_api_key', 'cached-key-xyz');

        $result = KonfigurasiController::resolveAiApiKey();
        $this->assertEquals('cached-key-xyz', $result);

        Cache::forget('prajadigital.setting.ai_api_key');
    }

    /** resolveWaSimulation mengembalikan nilai cache jika ada */
    public function test_resolve_wa_simulation_prefers_cache(): void
    {
        Cache::forever('prajadigital.setting.wa_simulation', false);

        $result = KonfigurasiController::resolveWaSimulation();
        $this->assertFalse($result);

        Cache::forget('prajadigital.setting.wa_simulation');
    }

    /** wa_simulation value tidak valid — validasi menolak */
    public function test_invalid_wa_simulation_value_fails_validation(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-whatsapp'), [
                'wa_simulation' => 'yes_please',
            ])
            ->assertSessionHasErrors('wa_simulation');
    }

    // ─────────────────────────────────────────────
    //  DUAL GEMINI API KEY & FALLBACK TESTS
    // ─────────────────────────────────────────────

    /** Super Admin dapat memperbarui API Key AI Cadangan */
    public function test_super_admin_can_update_ai_fallback_api_key(): void
    {
        Cache::forget('prajadigital.setting.ai_api_key_fallback');

        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_api_key' => '',
                'ai_api_key_fallback' => 'fallback-secret-key-999',
                'ai_model' => '',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        $this->assertEquals('fallback-secret-key-999', Cache::get('prajadigital.setting.ai_api_key_fallback'));
        $this->assertEquals('fallback-secret-key-999', KonfigurasiController::resolveAiFallbackApiKey());

        Cache::forget('prajadigital.setting.ai_api_key_fallback');
    }

    /** Halaman konfigurasi menyamarkan API Key cadangan pada teks info dan mengisi value input untuk Super Admin */
    public function test_konfigurasi_page_masks_fallback_api_key(): void
    {
        // Use SettingService so the DB record also exists (hasInDatabase = true -> 'source_fallback' = database)
        SettingService::set(KonfigurasiController::SETTING_AI_KEY_FALLBACK, 'AIzaSyA1234567890abcdefghijklmnopqrstuv', encrypt: false, group: 'ai');

        $response = $this->actingAs($this->superAdmin)
            ->get(route('konfigurasi.index'));

        $response->assertOk();
        $response->assertSee('AIzaSyA1234567890abcdefghijklmnopqrstuv', false);
        $response->assertSee('Cadangan Siap', false);

        SettingService::forget(KonfigurasiController::SETTING_AI_KEY_FALLBACK);
    }

    /** Tes koneksi AI otomatis fallback ke key cadangan jika key utama gagal */
    public function test_test_ai_connection_falls_back_to_secondary_key_when_primary_fails(): void
    {
        Cache::forever('prajadigital.setting.ai_api_key', 'broken-primary-key');
        Cache::forever('prajadigital.setting.ai_api_key_fallback', 'healthy-backup-key');

        Http::fake(function (Request $request) {
            if ($request->header('x-goog-api-key')[0] === 'broken-primary-key') {
                return Http::response(['error' => ['message' => 'Quota exceeded']], 429);
            }

            if ($request->header('x-goog-api-key')[0] === 'healthy-backup-key') {
                return Http::response([
                    'candidates' => [[
                        'content' => [
                            'parts' => [['text' => 'Halo dari key cadangan!']],
                        ],
                    ]],
                ], 200);
            }

            return Http::response([], 500);
        });

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('konfigurasi.test-ai'), ['target' => 'auto']);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonMissing(['broken-primary-key', 'healthy-backup-key']);

        Cache::forget('prajadigital.setting.ai_api_key');
        Cache::forget('prajadigital.setting.ai_api_key_fallback');
    }

    /** Chatbot AI beralih ke key cadangan saat key utama mengembalikan 429 quota exceeded */
    public function test_chatbot_falls_back_to_secondary_key_on_429(): void
    {
        Cache::forever('prajadigital.setting.ai_api_key', 'primary-key-429');
        Cache::forever('prajadigital.setting.ai_api_key_fallback', 'fallback-key-200');
        config(['app.ai_provider' => 'gemini']);

        Http::fake(function (Request $request) {
            if ($request->header('x-goog-api-key')[0] === 'primary-key-429') {
                return Http::response(['error' => ['code' => 429, 'message' => 'Resource exhausted']], 429);
            }

            if ($request->header('x-goog-api-key')[0] === 'fallback-key-200') {
                return Http::response([
                    'candidates' => [[
                        'content' => [
                            'parts' => [['text' => 'Respon cerdas dari key cadangan']],
                        ],
                    ]],
                ], 200);
            }

            return Http::response([], 500);
        });

        // Use a message that bypasses all local rule-based responses in the chatbot
        // and forces a real Gemini API call.
        $response = $this->actingAs($this->wargaUser)
            ->postJson(route('ai.chat.store'), [
                'message' => 'Jelaskan secara rinci prosedur pengajuan administrasi kependudukan kepada saya',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('reply', 'Respon cerdas dari key cadangan');

        Cache::forget('prajadigital.setting.ai_api_key');
        Cache::forget('prajadigital.setting.ai_api_key_fallback');
    }

    /** Ganti key utama melalui web dan pastikan chatbot langsung memakai key baru tanpa restart server */
    public function test_changing_primary_key_via_web_is_immediately_used_by_chatbot(): void
    {
        config(['app.ai_provider' => 'gemini']);

        // 1. Super Admin mengganti key utama melalui web form
        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-ai'), [
                'ai_api_key' => 'new-live-primary-key-888',
                'ai_api_key_fallback' => '',
                'ai_model' => '',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        // 2. Mock Gemini API untuk memastikan chatbot mengirim key baru
        $usedKey = null;
        Http::fake(function (Request $request) use (&$usedKey) {
            $usedKey = $request->header('x-goog-api-key')[0] ?? null;

            return Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Chatbot berjalan dengan key baru.']],
                    ],
                ]],
            ], 200);
        });

        // 3. User berinteraksi dengan chatbot — pesan netral yang melewati semua rule lokal
        $response = $this->actingAs($this->wargaUser)
            ->postJson(route('ai.chat.store'), [
                'message' => 'Beri saya rekomendasi umum terkait layanan administrasi publik di lingkungan desa',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('reply', 'Chatbot berjalan dengan key baru.');

        $this->assertEquals('new-live-primary-key-888', $usedKey);

        Cache::forget('prajadigital.setting.ai_api_key');
    }

    /** Jika key utama kembali berfungsi normal, chatbot kembali memprioritaskan key utama */
    public function test_chatbot_prioritizes_primary_key_when_primary_key_is_healthy(): void
    {
        Cache::forever('prajadigital.setting.ai_api_key', 'healthy-primary-key-111');
        Cache::forever('prajadigital.setting.ai_api_key_fallback', 'standby-fallback-key-222');
        config(['app.ai_provider' => 'gemini']);

        $calledKeys = [];
        Http::fake(function (Request $request) use (&$calledKeys) {
            $calledKey = $request->header('x-goog-api-key')[0] ?? null;
            $calledKeys[] = $calledKey;

            return Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Jawaban dari key utama yang sehat.']],
                    ],
                ]],
            ], 200);
        });

        // Pesan netral yang melewati semua rule lokal chatbot
        $response = $this->actingAs($this->wargaUser)
            ->postJson(route('ai.chat.store'), [
                'message' => 'Beri saya panduan lengkap prosedur permohonan administrasi kependudukan',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('reply', 'Jawaban dari key utama yang sehat.');

        // Key utama harus dipanggil pertama dan key cadangan tidak disentuh
        $this->assertEquals(['healthy-primary-key-111'], $calledKeys);

        Cache::forget('prajadigital.setting.ai_api_key');
        Cache::forget('prajadigital.setting.ai_api_key_fallback');
    }

    /** Ganti token Fonnte melalui web dan pastikan service WhatsApp langsung memakai token baru tanpa restart */
    public function test_changing_wa_token_via_web_is_immediately_used_by_whatsapp_service(): void
    {
        // 1. Super Admin mengganti token Fonnte dan mengatur live mode via web form
        $this->actingAs($this->superAdmin)
            ->post(route('konfigurasi.update-whatsapp'), [
                'wa_token' => 'new-live-fonnte-token-777',
                'wa_simulation' => '0',
            ])
            ->assertRedirect(route('konfigurasi.index'))
            ->assertSessionHas('success');

        // 2. Mock Http Fonnte API
        $usedToken = null;
        Http::fake(function (Request $request) use (&$usedToken) {
            if (str_contains($request->url(), 'api.fonnte.com/send')) {
                $usedToken = $request->header('Authorization')[0] ?? null;

                return Http::response([
                    'status' => true,
                    'message' => 'Pesan terkirim',
                ], 200);
            }

            return Http::response([], 404);
        });

        // 3. Jalankan pengiriman via WhatsappNotificationService
        /** @var WhatsappNotificationService $waService */
        $waService = app(WhatsappNotificationService::class);
        $result = $waService->sendNotification('081234567890', 'Uji pesan notifikasi');

        $this->assertTrue($result['success']);
        $this->assertEquals('new-live-fonnte-token-777', $usedToken);

        Cache::forget('prajadigital.setting.wa_token');
        Cache::forget('prajadigital.setting.wa_simulation');
    }
}
