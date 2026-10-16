<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

/**
 * Provider OpenAI (opsional). Aktif jika .env CHATBOT_PROVIDER=openai
 * dan OPENAI_API_KEY diisi. API key TIDAK ditulis di source code.
 */
class OpenAiChatbotProvider implements ChatbotProviderInterface
{
    public function name(): string
    {
        return 'openai';
    }

    public function ask(string $question, array $context = []): string
    {
        $apiKey = config('services.openai.key');
        $model = config('services.openai.model', 'gpt-4o-mini');

        if (blank($apiKey)) {
            throw new \RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        }

        $system = 'Kamu adalah tutor sekolah yang ramah. Jelaskan dengan bahasa sederhana untuk siswa SMP/SMA dalam Bahasa Indonesia. Maksimal 200 kata. Hanya jawab seputar materi pembelajaran.';
        if (! empty($context['ebook_title'])) {
            $system .= " Konteks e-book: {$context['ebook_title']}.";
        }

        $chunks = $context['content_chunks'] ?? [];
        if (! empty($chunks)) {
            $system .= "\n\nKutipan isi buku:\n".implode("\n---\n", array_slice($chunks, 0, 5));
        }

        if (! empty($context['video_titles'])) {
            $system .= "\nVideo terkait: ".implode(', ', $context['video_titles']).'.';
        }

        $messages = [['role' => 'system', 'content' => $system]];
        $history = $context['history'] ?? [];

        if (is_array($history)) {
            foreach ($history as $item) {
                if (! is_array($item) || blank($item['text'] ?? null)) {
                    continue;
                }

                $messages[] = [
                    'role' => ($item['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                    'content' => mb_substr((string) $item['text'], 0, 500),
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        $response = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.6,
                'max_tokens' => 400,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal menghubungi AI: '.$response->status());
        }

        return trim($response->json('choices.0.message.content') ?? 'Maaf, saya belum bisa menjawab.');
    }
}
