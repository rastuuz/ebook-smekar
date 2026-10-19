<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

/**
 * Ekstrak teks dari file PDF e-book (disk private `local`, fallback `public`
 * untuk file lama sebelum migrasi private).
 * Murni lokal, tanpa API. Gagal parse (misal PDF hasil scan)
 * mengembalikan null — upload TIDAK boleh gagal karenanya.
 */
class PdfTextExtractor
{
    public const MAX_CHARS = 200000;

    public const MIN_CHARS = 50;

    public function __construct(protected Parser $parser = new Parser) {}

    /**
     * @return array{text: string, pages: int}|null
     */
    public function extract(string $diskPath): ?array
    {
        try {
            // PDF besar sering butuh > 30 detik (max_execution_time default) untuk
            // parsing. set_time_limit(0) mereset timer mulai titik ekstraksi ini.
            set_time_limit(0);
            ignore_user_abort(true);

            $disk = Storage::disk('local')->exists($diskPath) ? 'local' : 'public';

            if (! Storage::disk($disk)->exists($diskPath)) {
                return null;
            }

            $absolute = Storage::disk($disk)->path($diskPath);
            $pdf = $this->parser->parseFile($absolute);
            $pages = $pdf->getPages();

            $perPage = [];
            foreach ($pages as $i => $page) {
                $text = trim(preg_replace('/\s+/u', ' ', $page->getText() ?? ''));
                if ($text !== '') {
                    $perPage[] = '[hal.'.($i + 1).'] '.$text;
                }
            }

            $full = trim(implode("\n\n", $perPage));

            if (mb_strlen($full) < self::MIN_CHARS) {
                return null; // kemungkinan PDF scan/gambar
            }

            if (mb_strlen($full) > self::MAX_CHARS) {
                $full = mb_substr($full, 0, self::MAX_CHARS);
            }

            return ['text' => $full, 'pages' => count($pages)];
        } catch (\Throwable) {
            return null;
        }
    }
}
