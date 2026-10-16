<?php

namespace App\Console\Commands;

use App\Models\Ebook;
use App\Services\PdfTextExtractor;
use Illuminate\Console\Command;

class ExtractEbookContent extends Command
{
    protected $signature = 'ebooks:extract-text {--fresh : Ekstrak ulang semua, termasuk yang sudah terindeks}';

    protected $description = 'Ekstrak teks PDF e-book untuk chatbot (backfill)';

    public function handle(PdfTextExtractor $extractor): int
    {
        $query = Ebook::whereNotNull('file');

        if (! $this->option('fresh')) {
            $query->whereNull('content_indexed_at');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('Tidak ada PDF yang perlu diekstrak.');

            return self::SUCCESS;
        }

        $ok = 0;
        $skip = 0;

        $this->withProgressBar($query->get(), function (Ebook $ebook) use ($extractor, &$ok, &$skip) {
            $result = $extractor->extract($ebook->file);

            if ($result === null) {
                $skip++;

                return;
            }

            $ebook->update([
                'content_text' => $result['text'],
                'content_indexed_at' => now(),
            ]);
            $ok++;
        });

        $this->newLine();
        $this->info("Selesai: {$ok} terindeks, {$skip} dilewati (scan/rusak).");

        return self::SUCCESS;
    }
}
