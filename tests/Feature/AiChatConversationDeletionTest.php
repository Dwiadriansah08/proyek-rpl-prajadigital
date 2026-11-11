<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatConversationDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $roleWarga = Role::create(['name' => 'warga']);

        $this->userA = User::factory()->create([
            'role_id' => $roleWarga->id,
            'name' => 'User A',
            'email' => 'usera@example.com',
        ]);

        $this->userB = User::factory()->create([
            'role_id' => $roleWarga->id,
            'name' => 'User B',
            'email' => 'userb@example.com',
        ]);
    }

    public function test_user_can_delete_own_conversation_successfully(): void
    {
        $conv = AiConversation::create([
            'user_id' => $this->userA->id,
            'title' => 'Pertanyaan Syarat Surat',
        ]);

        $msg1 = AiMessage::create([
            'conversation_id' => $conv->id,
            'role' => 'user',
            'content' => 'Bagaimana cara buat surat?',
        ]);

        $msg2 = AiMessage::create([
            'conversation_id' => $conv->id,
            'role' => 'assistant',
            'content' => 'Silakan lengkapi berkas...',
        ]);

        $response = $this->actingAs($this->userA)
            ->deleteJson(route('ai.chat.destroy', $conv->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Percakapan berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('ai_conversations', ['id' => $conv->id]);
        $this->assertDatabaseMissing('ai_messages', ['id' => $msg1->id]);
        $this->assertDatabaseMissing('ai_messages', ['id' => $msg2->id]);
    }

    public function test_user_cannot_delete_conversation_belonging_to_another_user(): void
    {
        $convB = AiConversation::create([
            'user_id' => $this->userB->id,
            'title' => 'Percakapan Rahasia User B',
        ]);

        $response = $this->actingAs($this->userA)
            ->deleteJson(route('ai.chat.destroy', $convB->id));

        $response->assertNotFound();

        $this->assertDatabaseHas('ai_conversations', ['id' => $convB->id]);
    }

    public function test_guest_cannot_delete_any_conversation(): void
    {
        $conv = AiConversation::create([
            'user_id' => $this->userA->id,
            'title' => 'Percakapan Tamu',
        ]);

        $response = $this->deleteJson(route('ai.chat.destroy', $conv->id));

        $response->assertUnauthorized();

        $this->assertDatabaseHas('ai_conversations', ['id' => $conv->id]);
    }

    public function test_deleting_non_existent_conversation_returns_404(): void
    {
        $response = $this->actingAs($this->userA)
            ->deleteJson(route('ai.chat.destroy', 999999));

        $response->assertNotFound();
    }

    public function test_user_can_delete_all_own_conversations_without_affecting_other_users(): void
    {
        $convA1 = AiConversation::create([
            'user_id' => $this->userA->id,
            'title' => 'Topik A1',
        ]);
        $msgA1 = AiMessage::create([
            'conversation_id' => $convA1->id,
            'role' => 'user',
            'content' => 'Pesan A1',
        ]);

        $convA2 = AiConversation::create([
            'user_id' => $this->userA->id,
            'title' => 'Topik A2',
        ]);
        $msgA2 = AiMessage::create([
            'conversation_id' => $convA2->id,
            'role' => 'user',
            'content' => 'Pesan A2',
        ]);

        $convB = AiConversation::create([
            'user_id' => $this->userB->id,
            'title' => 'Topik B Tetap Ada',
        ]);
        $msgB = AiMessage::create([
            'conversation_id' => $convB->id,
            'role' => 'user',
            'content' => 'Pesan B Tetap Ada',
        ]);

        $response = $this->actingAs($this->userA)
            ->deleteJson(route('ai.chat.destroy-all'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Seluruh riwayat percakapan berhasil dihapus.',
                'deleted_count' => 2,
            ]);

        $this->assertDatabaseMissing('ai_conversations', ['id' => $convA1->id]);
        $this->assertDatabaseMissing('ai_conversations', ['id' => $convA2->id]);
        $this->assertDatabaseMissing('ai_messages', ['id' => $msgA1->id]);
        $this->assertDatabaseMissing('ai_messages', ['id' => $msgA2->id]);

        $this->assertDatabaseHas('ai_conversations', ['id' => $convB->id]);
        $this->assertDatabaseHas('ai_messages', ['id' => $msgB->id]);
    }

    public function test_delete_all_when_user_has_no_conversations_returns_success(): void
    {
        $response = $this->actingAs($this->userA)
            ->deleteJson(route('ai.chat.destroy-all'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'deleted_count' => 0,
            ]);
    }

    public function test_guest_cannot_delete_all_conversations(): void
    {
        $response = $this->deleteJson(route('ai.chat.destroy-all'));

        $response->assertUnauthorized();
    }
}
