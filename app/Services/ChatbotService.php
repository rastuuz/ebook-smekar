<?php

namespace App\Services;

use App\Models\Ebook;
use App\Services\Chatbot\ChatbotProviderInterface;
use App\Services\Chatbot\ContentRetriever;
use App\Services\Chatbot\GeminiChatbotProvider;
use App\Services\Chatbot\LocalKnowledgeChatbotProvider;
use App\Services\Chatbot\OpenAiChatbotProvider;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function __construct(
        protected LocalKnowledgeChatbotProvider $local,
        protected GeminiChatbotProvider $gemini,
        protected OpenAiChatbotProvider $openai,
        protected ContentRetriever $retriever,
    ) {}

    public function provider(): ChatbotProviderInterface
    {
        return match (config('chatbot.provider', 'local')) {
            'gemini' => $this->gemini,
            'openai' => $this->openai,
            default => $this->local,
        };
    }

    /**
     * @param  list<array{role: string, text: string}>  $history  5 pesan terakhir dari frontend.
     */
    public function ask(string $question, ?Ebook $ebook = null, array $history = []): array
    {
        $question = trim(strip_tags($question));

        $context = $this->buildContext($question, $ebook, $this->sanitizeHistory($history));

        try {
            $answer = $this->provider()->ask($question, $context);

            return ['answer' => $answer, 'provider' => $this->provider()->name(), 'fallback' => false];
        } catch (\Throwable $e) {
            Log::warning('Chatbot provider gagal, fallback ke lokal: '.$e->getMessage());

            return [
                'answer' => $this->local->ask($question, $context),
                'provider' => 'local',
                'fallback' => true,
            ];
        }
    }

    /**
     * Bersihkan riwayat dari frontend: maksimal 5, peran valid, teks dibatasi.
     *
     * @return list<array{role: string, text: string}>
     */
    protected function sanitizeHistory(array $history): array
    {
        $clean = [];

        foreach ($history as $item) {
            if (! is_array($item)) {
                continue;
            }

            $role = $item['role'] ?? null;
            $text = trim(strip_tags((string) ($item['text'] ?? '')));

            if (! in_array($role, ['user', 'assistant'], true) || $text === '') {
                continue;
            }

            $clean[] = ['role' => $role, 'text' => mb_substr($text, 0, 500)];

            if (count($clean) >= 5) {
                break;
            }
        }

        return $clean;
    }

    protected function buildContext(string $question, ?Ebook $ebook, array $history = []): array
    {
        $context = ['history' => $history];

        if (! $ebook) {
            return $context;
        }

        $ebook->loadMissing(['category', 'videos']);

        // Hanya data materi — TIDAK ada NIS/nama/identitas siswa.
        $context['ebook'] = $ebook;
        $context['ebook_id'] = $ebook->id;
        $context['ebook_title'] = $ebook->title;
        $context['content_chunks'] = $this->retriever->chunksForPrompt($ebook, $question);
        $context['video_titles'] = $ebook->videos->take(3)->map(fn ($v) => $v->title)->all();

        return $context;
    }
}
