<?php

namespace App\Services\Chatbot;

use App\Models\Ebook;
use Illuminate\Support\Str;
use Sastrawi\Stemmer\StemmerFactory;

/**
 * Pencarian kata kunci di teks hasil ekstraksi PDF.
 * Dipakai provider lokal (kutipan + halaman) dan
 * ChatbotService (konteks untuk Gemini/OpenAI).
 *
 * Pencocokan 2 lapis: kata asli + bentuk dasar (Sastrawi),
 * lalu fallback fuzzy (toleransi 1-2 huruf salah ketik).
 */
class ContentRetriever
{
    public const SNIPPET_CHARS = 300;

    public const FUZZY_MIN_LENGTH = 5;

    public const FUZZY_MAX_DISTANCE = 2;

    public const FUZZY_MAX_WORDS_PER_BLOCK = 60;

    protected static $stemmer = null;

    protected static array $stemCache = [];

    public function stem(string $word): string
    {
        $key = Str::lower($word);

        if (isset(static::$stemCache[$key])) {
            return static::$stemCache[$key];
        }

        if (static::$stemmer === null) {
            static::$stemmer = (new StemmerFactory)->createStemmer();
        }

        $stemmed = static::$stemmer->stem($key);

        // Batasi memori (relevan untuk Octane): berhenti cache setelah 2000 kata.
        if (count(static::$stemCache) < 2000) {
            static::$stemCache[$key] = $stemmed;
        }

        return $stemmed;
    }

    /**
     * Varian pencocokan sebuah kata kunci: kata asli + bentuk dasar.
     * Bentuk dasar dipakai karena Sastrawi kadang over-stem
     * ("pergerakan" => "gera"), jadi keduanya dicek sebagai substring.
     *
     * @return list<string>
     */
    public function variants(string $keyword): array
    {
        $keyword = Str::lower($keyword);
        $stem = $this->stem($keyword);

        $variants = [$keyword];

        if ($stem !== '' && $stem !== $keyword && mb_strlen($stem) > 2) {
            $variants[] = $stem;
        }

        return array_values(array_unique($variants));
    }

    /**
     * Cocok jika kata asli ATAU bentuk dasarnya muncul di teks.
     */
    public function textMatches(string $haystack, string $keyword): bool
    {
        foreach ($this->variants($keyword) as $variant) {
            if (mb_stripos($haystack, $variant) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Posisi kemunculan paling awal dari semua varian kata kunci.
     */
    public function firstVariantPosition(string $haystack, string $keyword): int|false
    {
        $first = false;

        foreach ($this->variants($keyword) as $variant) {
            $pos = mb_stripos($haystack, $variant);

            if ($pos !== false && ($first === false || $pos < $first)) {
                $first = $pos;
            }
        }

        return $first;
    }

    /** @return list<string> */
    public function keywords(string $question): array
    {
        $stopwords = ['apa', 'apakah', 'yang', 'dimaksud', 'dengan', 'adalah', 'bagaimana', 'kenapa', 'gimana', 'mengapa', 'jelaskan', 'sebutkan', 'contoh', 'tentang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'pada', 'ini', 'itu', 'saya', 'kamu', 'tolong', '?'];
        $words = preg_split('/[^\p{L}\p{N}]+/u', Str::lower($question), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($words, fn ($w) => mb_strlen($w) > 2 && ! in_array($w, $stopwords)));
    }

    /**
     * @return list<array{page: int|null, snippet: string, keyword: string}>
     */
    public function retrieve(Ebook $ebook, string $question, int $limit = 4): array
    {
        if (blank($ebook->content_text)) {
            return [];
        }

        $keywords = $this->keywords($question);

        if (empty($keywords)) {
            return [];
        }

        $blocks = preg_split('/\n{2,}/u', $ebook->content_text) ?: [];
        $candidates = [];

        foreach ($blocks as $index => $block) {
            [$page, $clean] = $this->splitPageMarker($block);

            $matched = [];
            foreach ($keywords as $keyword) {
                if ($this->textMatches($clean, $keyword)) {
                    $matched[] = $keyword;
                }
            }

            if (empty($matched)) {
                continue;
            }

            // Skor: jumlah kata kunci cocok (bobot utama) + kecocokan frasa awal.
            $firstPos = PHP_INT_MAX;
            foreach ($matched as $keyword) {
                $pos = $this->firstVariantPosition($clean, $keyword);
                if ($pos !== false && $pos < $firstPos) {
                    $firstPos = $pos;
                }
            }

            $candidates[] = [
                'page' => $page,
                'snippet' => $this->makeSnippet($clean, $firstPos),
                'keyword' => implode(', ', $matched),
                'score' => count($matched) * 10 - min(9, intdiv($index, 10)),
            ];
        }

        // Fallback: tidak ada yang cocok persis → coba toleransi salah ketik.
        if (empty($candidates)) {
            $candidates = $this->fuzzyRetrieve($blocks, $keywords, $limit);
        }

        usort($candidates, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_map(
            fn ($h) => ['page' => $h['page'], 'snippet' => $h['snippet'], 'keyword' => $h['keyword']],
            array_slice($candidates, 0, $limit)
        );
    }

    /**
     * Pisahkan penanda "[hal.N]" dari awal blok teks.
     *
     * @return array{int|null, string}
     */
    protected function splitPageMarker(string $block): array
    {
        $page = null;
        $clean = $block;

        if (preg_match('/^\[hal\.(\d+)\]\s*/u', $clean, $m)) {
            $page = (int) $m[1];
            $clean = substr($clean, strlen($m[0]));
        }

        return [$page, $clean];
    }

    protected function makeSnippet(string $clean, int $centerPos): string
    {
        $start = max(0, $centerPos - 80);

        return trim(mb_substr($clean, $start, self::SNIPPET_CHARS));
    }

    /**
     * Pencarian fallback: kata yang mirip (jarak levenshtein kecil).
     * Hanya untuk kata kunci >= 5 huruf agar tidak over-match.
     *
     * @param  list<string>  $blocks
     * @param  list<string>  $keywords
     * @return list<array{page: int|null, snippet: string, keyword: string, score: int}>
     */
    protected function fuzzyRetrieve(array $blocks, array $keywords, int $limit): array
    {
        $candidates = [];

        $longKeywords = array_values(array_filter(
            $keywords,
            fn ($w) => mb_strlen($w) >= self::FUZZY_MIN_LENGTH
        ));

        if (empty($longKeywords)) {
            return [];
        }

        foreach ($blocks as $block) {
            [$page, $clean] = $this->splitPageMarker($block);

            foreach ($longKeywords as $keyword) {
                $word = $this->findFuzzyWord($clean, Str::lower($keyword));

                if ($word === null) {
                    continue;
                }

                $pos = mb_stripos($clean, $word);

                $candidates[] = [
                    'page' => $page,
                    'snippet' => $this->makeSnippet($clean, $pos === false ? 0 : $pos),
                    'keyword' => $keyword.' (~'.$word.')',
                    'score' => 4,
                ];
            }

            // Batasi kerja: cukup kumpulkan sedikit kandidat terbaik.
            if (count($candidates) >= $limit * 2) {
                break;
            }
        }

        return $candidates;
    }

    /**
     * Cari kata di teks yang mirip dengan kata kunci (toleransi typo).
     * Perbandingan ASCII lowercase agar levenshtein stabil untuk UTF-8.
     */
    protected function findFuzzyWord(string $haystack, string $keyword): ?string
    {
        $keywordAscii = Str::ascii($keyword);
        $len = strlen($keywordAscii);

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $haystack, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_slice($tokens, 0, self::FUZZY_MAX_WORDS_PER_BLOCK);

        foreach ($tokens as $token) {
            $tokenAscii = Str::ascii(Str::lower($token));

            if (strlen($tokenAscii) < 4) {
                continue;
            }

            // Filter cepat: beda panjang > jarak maks pasti tidak cocok.
            if (abs(strlen($tokenAscii) - $len) > self::FUZZY_MAX_DISTANCE) {
                continue;
            }

            if (levenshtein($tokenAscii, $keywordAscii) <= self::FUZZY_MAX_DISTANCE) {
                return $token;
            }
        }

        return null;
    }

    /** @return list<string> potongan "hal. X: ..." untuk prompt AI */
    public function chunksForPrompt(Ebook $ebook, string $question, int $limit = 8): array
    {
        return array_map(
            fn ($h) => ($h['page'] ? "[hal.{$h['page']}] " : '').$h['snippet'],
            $this->retrieve($ebook, $question, $limit)
        );
    }
}
