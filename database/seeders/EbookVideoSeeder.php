<?php

namespace Database\Seeders;

use App\Models\Ebook;
use App\Models\EbookVideo;
use Illuminate\Database\Seeder;

class EbookVideoSeeder extends Seeder
{
    public function run(): void
    {
        $ebook = Ebook::where('title', 'Dasar-Dasar Ilmu Pengetahuan')->first();

        if (! $ebook) {
            return;
        }

        $videos = [
            [
                'title' => 'Pengenalan Ilmu Pengetahuan',
                'youtube_url' => 'https://www.youtube.com/watch?v=aircAruvnKk',
                'description' => 'Video pengantar apa itu sains dan metode ilmiah.',
            ],
            [
                'title' => 'Materi Energi',
                'youtube_url' => 'https://www.youtube.com/watch?v=8yui9M8_V7M',
                'description' => 'Penjelasan bentuk-bentuk energi dan perubahannya.',
            ],
            [
                'title' => 'Sistem Tata Surya',
                'youtube_url' => 'https://www.youtube.com/watch?v=libKVRa01L8',
                'description' => 'Mengenal planet dan sistem tata surya.',
            ],
        ];

        foreach ($videos as $video) {
            EbookVideo::firstOrCreate(
                ['ebook_id' => $ebook->id, 'title' => $video['title']],
                array_merge(['ebook_id' => $ebook->id], $video)
            );
        }
    }
}
