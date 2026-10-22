<?php

namespace App\Services\Chatbot;

use App\Models\Ebook;

/**
 * Provider lokal tanpa API key.
 * Menjawab berbasis data e-book (judul, deskripsi, video) + panduan belajar.
 * Bukan hardcode jawaban: semua konten diambil dinamis dari database.
 */
class LocalKnowledgeChatbotProvider implements ChatbotProviderInterface
{
    public function __construct(protected ContentRetriever $retriever = new ContentRetriever) {}

    public function name(): string
    {
        return 'local';
    }

    public function ask(string $question, array $context = []): string
    {
        $question = trim($question);
        $ebook = $this->resolveEbook($context);

        // Kata kunci diambil dari pertanyaan + riwayat ("jelaskan itu lebih
        // lanjut" tetap nyambung ke topik sebelumnya). Tampilan tetap
        // memakai pertanyaan saat ini.
        $searchText = $question.' '.$this->historyUserText($context);
        $keywords = $this->retriever->keywords($searchText);

        if ($ebook) {
            $ebook->loadMissing(['category', 'videos']);

            // 1. Kutipan langsung dari isi buku (teks hasil ekstraksi PDF).
            $quotes = $this->retriever->retrieve($ebook, $searchText);

            if (! empty($quotes)) {
                return $this->answerFromBook($ebook, $quotes);
            }

            // 2. Kata kunci cocok deskripsi/judul video.
            $evidence = $this->findEvidence($ebook, $keywords);

            $intro = "Terkait e-book **{$ebook->title}** (".($ebook->category->name ?? 'Umum')."):\n\n";

            if ($evidence !== '') {
                $suffix = $ebook->hasIndexedContent()
                    ? "\n\nIsi bukunya belum membahas ini secara langsung — coba tanyakan dengan kata lain."
                    : "\n\nCatatan: teks isi buku ini belum tersedia"
                        .($ebook->file ? ' (PDF hasil scan tidak terbaca mesin)' : ' (belum ada file PDF)')
                        .'. Jawaban di atas dari deskripsi & video.';

                return $intro.$evidence.$suffix."\n\n".$this->studyTip($ebook);
            }

            return $intro.$this->genericExplain($question, $ebook);
        }

        return $this->genericExplain($question, null);
    }

    /**
     * Teks pertanyaan user dari riwayat (frontend-only, maks 5 pesan).
     */
    protected function historyUserText(array $context): string
    {
        $history = $context['history'] ?? [];

        if (! is_array($history)) {
            return '';
        }

        $texts = [];

        foreach ($history as $item) {
            if (is_array($item) && ($item['role'] ?? null) === 'user' && ! blank($item['text'] ?? null)) {
                $texts[] = (string) $item['text'];
            }
        }

        return implode(' ', $texts);
    }

    protected function resolveEbook(array $context): ?Ebook
    {
        if (isset($context['ebook']) && $context['ebook'] instanceof Ebook) {
            return $context['ebook'];
        }

        if (! empty($context['ebook_id'])) {
            return Ebook::with(['category', 'videos'])->find($context['ebook_id']);
        }

        return null;
    }

    /** @param list<array{page: int|null, snippet: string, keyword: string}> $quotes */
    protected function answerFromBook(Ebook $ebook, array $quotes): string
    {
        $lines = array_map(
            fn ($q) => '- “'.$q['snippet'].'”'.($q['page'] ? " (hal. {$q['page']})" : ''),
            $quotes
        );

        return "Dari isi e-book **{$ebook->title}**:\n"
            .implode("\n", $lines)
            ."\n\n".$this->studyTip($ebook);
    }

    /** @return list<string> */
    protected function keywords(string $question): array
    {
        return $this->retriever->keywords($question);
    }

    protected function findEvidence(Ebook $ebook, array $keywords): string
    {
        if (empty($keywords)) {
            return '';
        }

        $hits = [];

        foreach ($keywords as $keyword) {
            if ($ebook->description && $this->retriever->textMatches($ebook->description, $keyword)) {
                $hits[] = "Catatan e-book menyebut “{$keyword}” dalam deskripsi materi.";
            }
            foreach ($ebook->videos as $video) {
                if ($this->retriever->textMatches($video->title.' '.$video->description, $keyword)) {
                    $hits[] = "Video “{$video->title}” membahas “{$keyword}”.";
                }
            }
        }

        $hits = array_values(array_unique($hits));

        if (empty($hits)) {
            return '';
        }

        return "Yang saya temukan dari materi:\n- ".implode("\n- ", array_slice($hits, 0, 4));
    }

    protected function genericExplain(string $question, ?Ebook $ebook): string
    {
        $base = "Pertanyaan bagus! Mari kita uraikan langkah demi langkah:\n\n"
            ."1. **Pahami dulu istilahnya** — pecah pertanyaan “{$this->shorten($question)}” menjadi kata kunci.\n"
            ."2. **Hubungkan dengan materi** — baca kembali deskripsi e-book dan tonton video terkait.\n"
            ."3. **Buat contoh sendiri** — coba jelaskan dengan bahasamu dalam 2 kalimat.\n";

        if ($ebook) {
            $videoList = $ebook->videos->take(3)->map(fn ($v) => "“{$v->title}”")->implode(', ');
            $base .= "\nUntuk e-book ini, mulai dari: ".($videoList !== '' ? $videoList : 'deskripsi e-book').'.';
        }

        $base .= "\n\nTips: jika masih bingung, tanyakan lebih spesifik, misalnya “Apa yang dimaksud dengan istilah ini dalam buku {$this->bookName($ebook)}?”";

        return $base;
    }

    protected function bookName(?Ebook $ebook): string
    {
        return $ebook ? "“{$ebook->title}”" : 'yang sedang dibaca';
    }

    protected function studyTip(Ebook $ebook): string
    {
        return 'Tips belajar: baca 1 subbab, tonton 1 video (“'.($ebook->videos->first()?->title ?? 'video terkait').'”), lalu tulis ulang poin pentingnya.';
    }

    protected function shorten(string $text, int $limit = 80): string
    {
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit).'…' : $text;
    }
}
