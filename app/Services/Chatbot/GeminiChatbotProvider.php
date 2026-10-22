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

        $response = Http::timeout(90)->connectTimeout(10)->post($url, [
            'system_instruction' => [
                'parts' => [['text' => $this->systemPrompt($context)]],
            ],
            'contents' => $this->contentsWithHistory($question, $context),
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 2048,
                'thinkingConfig' => ['thinkingLevel' => 'low'],
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
        $prompt = 'Kamu tutor belajar di perpustakaan digital sekolah (E-Library SMKN 1 Dukuhturi). '
            .'Siswa bertanya seputar isi e-book yang sedang dibacanya. Jawab dalam Bahasa Indonesia sederhana, '
            .'ramah, dan ringkas (maksimal 200 kata), untuk siswa SMP/SMA/SMK. '
            .'ATURAN WAJIB: '
            .'1) Jawab HANYA berdasarkan "Konteks isi buku" di bawah — jawaban harus konsisten dengan materi yang dikutip; jangan menambah/mengarang isi buku. '
            .'2) Langsung ke inti: TIDAK ADA sapaan pembuka ("Halo!/Assalamualaikum/Selamat datang/Di sini"), '
            .'TIDAK ADA pembukaan tentang buku ("Berdasarkan e-book ... karya ..."), TIDAK ADA kalimat penutup '
            .'atau tawaran tambahan ("Ada lagi yang mau ditanyakan?"/"Semoga membantu"). '
            .'Mulai jawaban langsung dari isi, misalnya daftar poin atau penjelasan singkat. '
            .'3) Gunakan daftar poin pendek (1., 2., 3.) atau paragraf pendek yang rapi; boleh **tebal** untuk istilah kunci; tidak pakai markdown lain (tanpa judul #, tanpa tabel). '
            .'4) Kalau memakai konteks, sebutkan sumbernya, misalnya: (hal. 12). '
            .'5) Kalau jawaban TIDAK ada di konteks: langsung katakan "Poin ini belum saya temukan di e-book ini", sebutkan materi yang ada di buku bila membantu, lalu sarankan membaca bagian terdekat — JANGAN mengarang dari pengetahuan umum. '
            .'6) Jika diminta membuat latihan soal/daftar, buat persis sesuai jumlah yang diminta sampai selesai (tidak boleh terpotong). '
            .'7) Jika siswa menuliskan jawaban latihan sebelumnya, periksa langsung: mulai dari "Benar:" atau "Belum lengkap:" lalu jelaskan poin mana yang perlu dilengkapi. '
            .'8) Hanya jawab seputar materi pembelajaran. Tolak sopan pertanyaan di luar materi atau yang meminta data pribadi (NIS, nama siswa, password). '
            .'9) Jangan menyebut dirimu AI/kecerdasan buatan; hanya fitur tanya jawab materi perpustakaan.';

        $header = [];
        if (! empty($context['ebook_title'])) {
            $header[] = 'Judul: '.$context['ebook_title'];
        }
        if (! empty($context['ebook_author'])) {
            $header[] = 'Penulis: '.$context['ebook_author'];
        }
        if (! empty($context['ebook_category'])) {
            $header[] = 'Kategori: '.$context['ebook_category'];
        }

        $chunks = $context['content_chunks'] ?? [];
        if (! empty($chunks)) {
            $prompt .= "\n\nBuku yang sedang dibaca siswa: ".implode('; ', $header).".\n\n"
                ."Konteks isi buku (kutipan langsung, [hal.N] = halaman sumber):\n"
                .implode("\n---\n", array_slice($chunks, 0, 8));
        } elseif (! empty($header)) {
            $prompt .= "\n\nBuku yang sedang dibaca siswa: ".implode('; ', $header)
                .".\nTeks isi buku ini belum bisa dibaca (kemungkinan hasil scan), jadi jawab umum dan ringkas lalu arahkan siswa membaca PDF-nya sendiri.";
        }

        if (! empty($context['video_titles'])) {
            $prompt .= "\nVideo terkait: ".implode(', ', $context['video_titles']).'.';
        }

        return $prompt;
    }
}
