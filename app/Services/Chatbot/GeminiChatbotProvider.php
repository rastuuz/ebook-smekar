<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

/**
 * Provider Google Gemini (paket gratis via AI Studio).
 * API key di .env (GEMINI_API_KEY), TIDAK di source code.
 * Gagal/kuota habis -> ChatbotService otomatis fallback ke lokal.
 */
class GeminiChatbotProvider implements ChatbotProviderInterface
{
    public function name(): string
    {
        return 'gemini';
    }

    public function ask(string $question, array $context = []): string
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-2.0-flash');

        if (blank($apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY belum dikonfigurasi.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($apiKey);

        $response = Http::timeout(25)->post($url, [
            'system_instruction' => [
                'parts' => [['text' => $this->systemPrompt($context)]],
            ],
            'contents' => $this->contentsWithHistory($question, $context),
            'generationConfig' => [
                'temperature' => 0.5,
                'maxOutputTokens' => 400,
            ],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal menghubungi Gemini: '.$response->status());
        }

        $text = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')->implode('');

        return trim($text) !== '' ? trim($text) : 'Maaf, saya belum bisa menjawab.';
    }

    /**
     * Susun riwayat frontend menjadi format contents Gemini
     * (user => user, assistant => model) + pertanyaan saat ini.
     */
    protected function contentsWithHistory(string $question, array $context): array
    {
        $contents = [];
        $history = $context['history'] ?? [];

        if (is_array($history)) {
            foreach ($history as $item) {
                if (! is_array($item) || blank($item['text'] ?? null)) {
                    continue;
                }

                $contents[] = [
                    'role' => ($item['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => mb_substr((string) $item['text'], 0, 500)]],
                ];
            }
        }

        $contents[] = ['parts' => [['text' => $question]]];

        return $contents;
    }

    protected function systemPrompt(array $context): string
    {
        $prompt = 'Kamu tutor sekolah yang ramah. Jawab Bahasa Indonesia sederhana untuk siswa SMP/SMA, maksimal 200 kata. '
            .'Hanya jawab seputar materi pembelajaran. Tolak sopan pertanyaan di luar materi atau yang meminta data pribadi (NIS, nama siswa, password).';

        $chunks = $context['content_chunks'] ?? [];
        if (! empty($chunks)) {
            $prompt .= "\n\nKonteks isi buku:\n".implode("\n---\n", array_slice($chunks, 0, 5));
        } elseif (! empty($context['ebook_title'])) {
            $prompt .= "\n\nKonteks e-book: {$context['ebook_title']}.";
        }

        if (! empty($context['video_titles'])) {
            $prompt .= "\nVideo terkait: ".implode(', ', $context['video_titles']).'.';
        }

        return $prompt;
    }
}
