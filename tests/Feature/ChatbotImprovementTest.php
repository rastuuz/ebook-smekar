<?php

use App\Models\Ebook;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('stemming: pertanyaan berimbuhan tetap menemukan kutipan', function () {
    $ebook = Ebook::first();
    // Kata "pergerakan" tidak ada mentah di teks; bentuk dasarnya cocok "gerak".
    $ebook->update(['content_text' => '[hal.2] Energi gerak menggerakkan benda di sekitar kita.']);

    $this->actingAs(User::where('role', User::ROLE_SISWA)->first());
    $response = $this->postJson(route('chatbot.ask'), [
        'question' => 'Jelaskan pergerakan benda?',
        'ebook_id' => $ebook->id,
    ])->assertOk()->assertJsonPath('provider', 'local');

    expect($response->json('answer'))->toContain('hal. 2');
});

test('fuzzy: satu huruf salah ketik tetap menemukan kutipan', function () {
    $ebook = Ebook::first();
    $ebook->update(['content_text' => '[hal.3] Energi kinetik adalah energi yang dimiliki benda bergerak.']);

    $this->actingAs(User::where('role', User::ROLE_SISWA)->first());
    $response = $this->postJson(route('chatbot.ask'), [
        'question' => 'Apa itu enrgi kinetik?',
        'ebook_id' => $ebook->id,
    ])->assertOk()->assertJsonPath('provider', 'local');

    expect($response->json('answer'))->toContain('hal. 3');
});

test('riwayat: pertanyaan lanjutan nyambung ke topik sebelumnya', function () {
    $ebook = Ebook::first();
    $ebook->update(['content_text' => '[hal.3] Energi kinetik adalah energi yang dimiliki benda bergerak.']);

    $this->actingAs(User::where('role', User::ROLE_SISWA)->first());

    // Tanpa riwayat: kata ganti "itu" tidak menunjuk ke mana-mana → generik.
    $plain = $this->postJson(route('chatbot.ask'), [
        'question' => 'Jelaskan itu lebih lanjut?',
        'ebook_id' => $ebook->id,
    ])->assertOk();
    expect($plain->json('answer'))->not->toContain('hal. 3');

    // Dengan riwayat: kata kunci pertanyaan sebelumnya ikut dipakai → kutipan.
    $withHistory = $this->postJson(route('chatbot.ask'), [
        'question' => 'Jelaskan itu lebih lanjut?',
        'ebook_id' => $ebook->id,
        'history' => [
            ['role' => 'user', 'text' => 'Apa itu energi kinetik?'],
            ['role' => 'assistant', 'text' => 'Energi kinetik adalah energi gerak.'],
        ],
    ])->assertOk();
    expect($withHistory->json('answer'))->toContain('hal. 3');
});

test('riwayat lebih dari 5 item ditolak validasi', function () {
    $this->actingAs(User::where('role', User::ROLE_SISWA)->first());

    $history = array_fill(0, 6, ['role' => 'user', 'text' => 'Apa itu energi?']);

    $this->postJson(route('chatbot.ask'), [
        'question' => 'Apa itu energi kinetik?',
        'history' => $history,
    ])->assertStatus(422)->assertJsonValidationErrors('history');
});
