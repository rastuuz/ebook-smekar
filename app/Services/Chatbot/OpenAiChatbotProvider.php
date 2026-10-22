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

        $system = 'Kamu tutor belajar di perpustakaan digital sekolah (E-Library SMKN 1 Dukuhturi). '
            .'Siswa bertanya seputar isi e-book yang sedang dibacanya. Jawab dalam Bahasa Indonesia sederhana, ramah, '
            .'dan ringkas (maksimal 200 kata) untuk siswa SMP/SMA/SMK. '
            .'ATURAN WAJIB: '
            .'1) Jawab HANYA berdasarkan "Kutipan isi buku" di bawah; jangan menambah/mengarang isi buku. '
            .'2) Langsung ke inti: tidak ada sapaan pembuka ("Halo!"), tidak ada pembukaan tentang buku, tidak ada kalimat penutup atau tawaran tambahan ("Ada lagi?"/"Semoga membantu"). '
            .'3) Gunakan daftar poin pendek (1., 2., 3.) yang rapi; boleh **tebal** untuk istilah kunci. '
            .'4) Kalau memakai kutipan, sebutkan sumbernya, misalnya: (hal. 12). '
            .'5) Kalau jawaban tidak ada di kutipan, katakan jujur dan sarankan siswa membaca bagian terkait — jangan mengarang dari pengetahuan umum. '
            .'Jika diminta membuat latihan soal/daftar, buat persis sesuai jumlah yang diminta sampai selesai (tidak boleh terpotong). '
            .'Jika siswa menjawab latihan, periksa jawabannya dengan ramah. '
            .'Hanya jawab seputar materi pembelajaran; tolak sopan pertanyaan di luar materi atau yang meminta data pribadi. '
            .'Jangan menyebut dirimu AI; identitasmu hanya fitur tanya jawab materi perpustakaan.';

        $header = [];
        if (! empty($context['ebook_title'])) {
            $header[] = 'Judul: '.$context['ebook_title'];
        }
        if (! empty($context['ebook_author'])) {
            $header[] = 'Penulis: '.$context['ebook_author'];
        }

        $chunks = $context['content_chunks'] ?? [];
        if (! empty($chunks)) {
            $system .= "\n\nBuku yang sedang dibaca siswa: ".implode('; ', $header).".\n\n"
                ."Kutipan isi buku ([hal.N] = halaman sumber):\n"
                .implode("\n---\n", array_slice($chunks, 0, 8));
        } elseif (! empty($context['ebook_title'])) {
            $system .= "\n\nBuku yang sedang dibaca siswa: ".implode('; ', $header)
                .". Teks isi bukunya belum bisa dibaca, jadi jawab umum dan sarankan membaca PDF-nya.";
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
                'temperature' => 0.3,
                'max_tokens' => 1024,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Gagal menghubungi AI: '.$response->status());
        }

        return trim($response->json('choices.0.message.content') ?? 'Maaf, saya belum bisa menjawab.');
    }
}
