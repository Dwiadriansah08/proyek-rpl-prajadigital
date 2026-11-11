<?php

namespace Tests\Feature;

use App\Http\Controllers\AiChatController;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Desa;
use App\Models\Role;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AiAssistantEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminDesa;

    private User $superAdmin;

    private User $wargaUser;

    private Role $roleAdminDesa;

    private Role $roleSuperAdmin;

    private Role $roleWarga;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleSuperAdmin = Role::create(['name' => 'super_admin']);
        $this->roleAdminDesa = Role::create(['name' => 'admin_desa']);
        Role::create(['name' => 'rw']);
        Role::create(['name' => 'rt']);
        $this->roleWarga = Role::create(['name' => 'warga']);

        Desa::create([
            'nama' => 'Desa Lemahabang',
            'kecamatan' => 'Kecamatan Lemahabang',
            'kabupaten_kota' => 'Kabupaten Cirebon',
            'provinsi' => 'Jawa Barat',
            'alamat' => 'Jl. Raya Lemahabang No. 1',
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Bapak Super Admin',
            'role_id' => $this->roleSuperAdmin->id,
            'email' => 'superadmin@prajadigital.desa.id',
        ]);

        $this->adminDesa = User::factory()->create([
            'name' => 'Ibu Admin Desa',
            'role_id' => $this->roleAdminDesa->id,
            'email' => 'admindesa@desa.com',
        ]);

        $this->wargaUser = User::factory()->create([
            'name' => 'Warga Pengguna',
            'role_id' => $this->roleWarga->id,
            'email' => 'warga@desa.id',
        ]);
    }

    /**
     * GeminiService builds dynamic system instruction with real-time role statistics & Admin Desa context.
     */
    public function test_gemini_service_injects_realtime_role_stats_and_admin_desa(): void
    {
        $service = app(GeminiService::class);
        $instruction = $service->buildSystemInstruction($this->adminDesa, 'Halo AI');

        $this->assertStringContainsString('AI Assistant Desa Lemahabang', $instruction);
        $this->assertStringContainsString('KONTEKS REAL-TIME DATA Desa Lemahabang', $instruction);
        $this->assertStringContainsString('Total Akun Admin Desa: 1 (Nama: Ibu Admin Desa)', $instruction);
        $this->assertStringContainsString('Total Akun Super Admin: 1 (Nama: Bapak Super Admin)', $instruction);
        $this->assertStringContainsString('admin_desa', $instruction);
        $this->assertStringContainsString('format Markdown yang rapi', $instruction);
    }

    /**
     * AiChatController recognizes admin_desa role and injects real-time stats into buildContext.
     */
    public function test_ai_chat_controller_build_context_recognizes_admin_desa(): void
    {
        $controller = new AiChatController;
        $method = new ReflectionMethod(AiChatController::class, 'buildContext');
        $method->setAccessible(true);

        $prompt = $method->invoke($controller, $this->adminDesa, 'Tanya surat desa');

        $this->assertStringContainsString('admin_desa', $prompt);
        $this->assertStringContainsString('Admin Desa (Lurah/Staf Kantor Desa Lemahabang)', $prompt);
        $this->assertStringContainsString('Total Akun Admin Desa: 1', $prompt);
        $this->assertStringContainsString('Ibu Admin Desa', $prompt);
    }

    /**
     * AiAssistantController returns structured JSON with rendered markdown reply_html.
     */
    public function test_ai_assistant_controller_chat_endpoint_returns_markdown_html(): void
    {
        $response = $this->actingAs($this->wargaUser)->postJson(route('ai.assistant.chat'), [
            'message' => 'Bagaimana cara mengajukan surat domisili?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'conversation_id',
            'reply',
            'reply_html',
            'user_message' => ['role', 'content'],
            'ai_message' => ['id', 'role', 'content', 'html'],
        ]);
        $this->assertNotEmpty($response->json('reply_html'));
    }

    /**
     * AiChatController show endpoint returns markdown-rendered content_html for assistant messages.
     */
    public function test_ai_chat_controller_show_returns_markdown_html(): void
    {
        $conversation = AiConversation::create([
            'user_id' => $this->wargaUser->id,
            'title' => 'Test Percakapan',
        ]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Tanya syarat',
        ]);

        AiMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => "**Persyaratan:**\n- KTP Asli\n- Kartu Keluarga",
        ]);

        $response = $this->actingAs($this->wargaUser)->getJson(route('ai.chat.show', $conversation->id));

        $response->assertStatus(200);
        $messages = $response->json('messages');
        $this->assertCount(2, $messages);
        $assistantMsg = $messages[1];
        $this->assertEquals('assistant', $assistantMsg['role']);
        $this->assertStringContainsString('<strong>Persyaratan:</strong>', $assistantMsg['content_html']);
        $this->assertStringContainsString('<li>KTP Asli</li>', $assistantMsg['content_html']);
    }

    /**
     * Layout renders the ai assistant widget component with markdown bubble styles.
     */
    public function test_layout_renders_ai_assistant_widget_component(): void
    {
        $response = $this->actingAs($this->wargaUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="chatbotFab"', false);
        $response->assertSee('id="chatbotPanel"', false);
        $response->assertSee('id="cbMsgInput"', false);
        $response->assertSee('.cb-msg-bubble-ai p', false);
    }

    /**
     * AI Assistant correctly identifies the logged-in user when asked "siapa saya".
     */
    public function test_ai_identifies_logged_in_user_when_asked_who_am_i(): void
    {
        // 1. Warga User
        $responseWarga = $this->actingAs($this->wargaUser)->postJson(route('ai.assistant.chat'), [
            'message' => 'siapa saya?',
        ]);

        $responseWarga->assertStatus(200);
        $this->assertStringContainsString('Warga Pengguna', $responseWarga->json('reply'));
        $this->assertStringContainsString('warga@desa.id', $responseWarga->json('reply'));

        // 2. Admin Desa User
        $responseAdmin = $this->actingAs($this->adminDesa)->postJson(route('ai.chat.store'), [
            'message' => 'siapa saya?',
        ]);

        $responseAdmin->assertStatus(200);
        $this->assertStringContainsString('Ibu Admin Desa', $responseAdmin->json('reply'));
        $this->assertStringContainsString('Admin Desa', $responseAdmin->json('reply'));
        $this->assertStringContainsString('admindesa@desa.com', $responseAdmin->json('reply'));
    }

    /**
     * System instruction injects user identity and real-time pengajuan surat statistics.
     */
    public function test_system_instruction_injects_user_identity_and_surat_statistics(): void
    {
        $service = app(GeminiService::class);
        $instruction = $service->buildSystemInstruction($this->adminDesa, 'Cek status data');

        $this->assertStringContainsString('KONTEKS PENGGUNA YANG SEDANG CHAT SAMA KAMU:', $instruction);
        $this->assertStringContainsString('Nama: Ibu Admin Desa', $instruction);
        $this->assertStringContainsString('Role: ADMIN_DESA', $instruction);
        $this->assertStringContainsString('Email: admindesa@desa.com', $instruction);
        $this->assertStringContainsString('Statistik Pengajuan Surat: Total', $instruction);
        $this->assertStringContainsString("Jika user bertanya 'siapa saya', 'siapa nama saya', atau 'apa role saya', JAWAB SESUAI DATA PENGGUNA LOGGED-IN DI ATAS", $instruction);
    }
}
