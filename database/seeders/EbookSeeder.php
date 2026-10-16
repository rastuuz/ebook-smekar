<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ebook;
use Illuminate\Database\Seeder;

class EbookSeeder extends Seeder
{
    public function run(): void
    {
        $ipa = Category::where('name', 'Ilmu Pengetahuan Alam')->first();
        $matematika = Category::where('name', 'Matematika')->first();
        $informatika = Category::where('name', 'Informatika')->first();

        $ebooks = [
            [
                'category_id' => $ipa?->id,
                'title' => 'Dasar-Dasar Ilmu Pengetahuan',
                'author' => 'Tim Sains Sekolah',
                'description' => 'Pengantar ilmu pengetahuan: energi, tata surya, ekosistem, dan metode ilmiah untuk siswa.',
            ],
            [
                'category_id' => $matematika?->id,
                'title' => 'Matematika Dasar Kelas X',
                'author' => 'Drs. H. Ahmad Yani',
                'description' => 'Materi aljabar, persamaan linear, fungsi, dan geometri dasar disertai contoh soal.',
            ],
            [
                'category_id' => $informatika?->id,
                'title' => 'Pengenalan Informatika',
                'author' => 'Rina Komalasari, S.Kom',
                'description' => 'Dasar komputer, berpikir komputasional, internet sehat, dan pengantar pemrograman.',
            ],
        ];

        foreach ($ebooks as $ebook) {
            if (! $ebook['category_id']) {
                continue;
            }

            Ebook::firstOrCreate(
                ['title' => $ebook['title']],
                $ebook
            );
        }
    }
}
