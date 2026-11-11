<?php

namespace Tests\Unit;

use App\Http\Controllers\AiChatController;
use App\Models\Role;
use App\Models\SuratTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AiChatMockResponseTest extends TestCase
{
    use RefreshDatabase;

    private function invokeGetMockAiResponse($user, string $message): string
    {
        $controller = new AiChatController;
        $method = new ReflectionMethod(AiChatController::class, 'getMockAiResponse');

        return $method->invoke($controller, 1, $user, $message);
    }

    public function test_mock_rejects_python_requests(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->invokeGetMockAiResponse($user, 'Tolong buatkan script python');

        $this->assertStringContainsString('Maaf, saya tidak dapat membantu membuat atau memberikan kode Python', $response);
    }

    public function test_mock_rejects_general_coding_requests(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->invokeGetMockAiResponse($user, 'Bisa bantu coding PHP query SQL?');

        $this->assertStringContainsString('Maaf, saya adalah Asisten Informasi dan Layanan Desa', $response);
        $this->assertStringContainsString('tidak dapat memberikan source code', $response);
    }

    public function test_mock_provides_domisili_letter_guidance(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->invokeGetMockAiResponse($user, 'Bagaimana cara membuat surat pengantar domisili?');

        $this->assertEquals('Halo, untuk membuat surat pengantar domisili silakan ajukan melalui menu Layanan Surat.', $response);
    }

    public function test_mock_lists_active_letter_templates(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        SuratTemplate::create([
            'nama_surat' => 'Surat Keterangan Usaha',
            'kode_surat' => 'SKU',
            'is_active' => true,
        ]);

        $response = $this->invokeGetMockAiResponse($user, 'Apa saja layanan surat yang tersedia?');

        $this->assertStringContainsString('Surat Keterangan Usaha', $response);
        $this->assertStringContainsString('Layanan Surat', $response);
    }

    public function test_mock_answers_procedure_steps(): void
    {
        $role = Role::create(['name' => 'warga']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->invokeGetMockAiResponse($user, 'Bagaimana langkah pengajuan surat?');

        $this->assertStringContainsString('Layanan Surat', $response);
        $this->assertStringContainsString('Buat Pengajuan Surat', $response);
        $this->assertStringContainsString('Kirim Pengajuan', $response);
    }
}
