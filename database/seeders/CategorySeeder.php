<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Ilmu Pengetahuan Alam', 'description' => 'Fisika, biologi, kimia, dan sains dasar.'],
            ['name' => 'Matematika', 'description' => 'Aljabar, geometri, statistika, dan logika.'],
            ['name' => 'Bahasa Indonesia', 'description' => 'Literasi, tata bahasa, dan sastra Indonesia.'],
            ['name' => 'Bahasa Inggris', 'description' => 'Grammar, vocabulary, dan reading comprehension.'],
            ['name' => 'Ilmu Pengetahuan Sosial', 'description' => 'Sejarah, geografi, dan ekonomi.'],
            ['name' => 'Informatika', 'description' => 'Komputer dasar, pemrograman, dan internet sehat.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
