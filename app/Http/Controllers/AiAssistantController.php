<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    /**
     * Kirim pesan ke AI Assistant Desa dan terima jawaban terformat Markdown.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        if ($user) {
            $user->loadMissing(['role', 'rw', 'rt', 'warga']);
        }

        $message = trim($validated['message']);
        $conversationId = $validated['conversation_id'] ?? null;

        $conversation = null;
        if ($conversationId) {
            $conversation = AiConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (! $conversation) {
            $conversation = AiConversation::create([
                'user_id' => $user->id,
                'title' => mb_substr($message, 0, 50).(mb_strlen($message) > 50 ? '...' : ''),
            ]);
            $conversationId = $conversation->id;
        }

        $userMsg = AiMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'user',
            'content' => $message,
        ]);

        $history = $conversation->messages()
            ->latest('id')
            ->take(10)
            ->get()
            ->reverse()
            ->toArray();

        try {
            $provider = config('app.ai_provider', env('AI_PROVIDER', 'mock'));
            if ($provider === 'mock') {
                $reply = app(AiChatController::class)->getMockAiResponse($conversationId, $user, $message);
            } else {
                $reply = $this->geminiService->generateResponse($user, $message, $history);
                if (str_starts_with($reply, 'Halo! Asisten AI sedang mengalami') ||
                    str_starts_with($reply, 'Maaf,')) {
                    $reply = app(AiChatController::class)->getMockAiResponse($conversationId, $user, $message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('AI assistant chat error: '.$e->getMessage(), ['user_id' => $user->id]);
            $reply = app(AiChatController::class)->getMockAiResponse($conversationId, $user, $message);
        }

        $aiMsg = AiMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $reply,
        ]);

        $replyHtml = Str::markdown($reply, ['html_input' => 'escape']);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId,
            'conversation_title' => $conversation->title,
            'reply' => $reply,
            'reply_html' => $replyHtml,
            'user_message' => [
                'id' => $userMsg->id,
                'role' => 'user',
                'content' => $message,
            ],
            'ai_message' => [
                'id' => $aiMsg->id,
                'role' => 'assistant',
                'content' => $reply,
                'content_html' => $replyHtml,
                'html' => $replyHtml,
            ],
        ]);
    }
}
