<?php

use App\Models\Category;
use App\Models\Ebook;
use App\Models\User;
use App\Services\ChatbotService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('guest home ok, dashboard redirect', function () {
    $this->get('/')->assertOk();
    $this->get('/dashboard')->assertRedirect('/login-siswa');
});

test('siswa login NIS valid + akses katalog, ditolak admin area', function () {
    $this->post(route('login.nis.store'), ['nis' => '18034'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs(User::where('nis', '18034')->first());

    $this->get(route('ebooks.index'))->assertOk();
    $ebook = Ebook::first();
    $this->get(route('ebooks.show', $ebook))->assertOk();
    $this->get(route('admin.ebooks.index'))->assertForbidden();
    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('NIS salah ditolak', function () {
    $this->post(route('login.nis.store'), ['nis' => '99999'])
        ->assertSessionHasErrors('nis');
    $this->assertGuest();
});

test('guru seperti siswa + menu video, tanpa hak kelola', function () {
    $this->post(route('login.guru.store'), ['username' => 'guru', 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs(User::where('username', 'guru')->first());

    // Area baca guru: dashboard guru, katalog, detail, video.
    $this->get(route('dashboard'))->assertOk()->assertSee('Dashboard Guru', false);
    $this->get(route('ebooks.index'))->assertOk();
    $ebook = Ebook::first();
    $this->get(route('ebooks.show', $ebook))->assertOk();
    $this->get(route('videos.index'))->assertOk();

    // Guru tidak boleh menyentuh area kelola admin.
    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('admin.ebooks.index'))->assertForbidden();
    $this->get(route('admin.categories.index'))->assertForbidden();
    $this->get(route('admin.videos.index'))->assertForbidden();
    $this->get(route('admin.users.index'))->assertForbidden();
});

test('admin full akses users', function () {
    $this->post(route('login.admin.store'), ['username' => 'admin', 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));
    $this->get(route('admin.users.index'))->assertOk();
    $this->get(route('admin.categories.index'))->assertOk();
});

test('staff password salah ditolak', function () {
    $this->post(route('login.admin.store'), ['username' => 'admin', 'password' => 'salah'])
        ->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('chatbot jawab untuk siswa', function () {
    $this->actingAs(User::where('nis', '18034')->first());
    $ebook = Ebook::first();
    $this->postJson(route('chatbot.ask'), [
        'question' => 'Apa yang dimaksud dengan energi?',
        'ebook_id' => $ebook->id,
    ])->assertOk()->assertJsonStructure(['answer', 'provider']);
});

test('admin bisa upload cover + pdf, tolak file bukan pdf', function () {
    Storage::fake('public');
    Storage::fake('local');
    $admin = User::where('role', User::ROLE_ADMIN)->first();
    $category = Category::first();

    $this->actingAs($admin)->post(route('admin.ebooks.store'), [
        'category_id' => $category->id,
        'title' => 'E-Book Upload Test',
        'author' => 'Tester',
        'cover' => UploadedFile::fake()->image('cover.jpg'),
        'file' => UploadedFile::fake()->create('materi.pdf', 100, 'application/pdf'),
    ])->assertRedirect(route('admin.ebooks.index'));

    $ebook = Ebook::where('title', 'E-Book Upload Test')->first();
    expect($ebook)->not->toBeNull();
    // Cover public (thumbnail), PDF private (hanya via controller ber-auth).
    Storage::disk('public')->assertExists($ebook->cover);
    Storage::disk('local')->assertExists($ebook->file);

    $this->actingAs($admin)->post(route('admin.ebooks.store'), [
        'category_id' => $category->id,
        'title' => 'E-Book Gagal',
        'author' => 'Tester',
        'file' => UploadedFile::fake()->create('doc.txt', 10, 'text/plain'),
    ])->assertSessionHasErrors('file');
});

test('chatbot lokal mengutip isi buku + halaman', function () {
    $ebook = Ebook::first();
    $ebook->update(['content_text' => "[hal.3] Energi kinetik adalah energi yang dimiliki benda bergerak.\n\n[hal.4] Energi potensial tersimpan pada benda."]);

    $this->actingAs(User::where('role', User::ROLE_SISWA)->first());
    $response = $this->postJson(route('chatbot.ask'), [
        'question' => 'Apa itu energi kinetik?',
        'ebook_id' => $ebook->id,
    ])->assertOk()->assertJsonPath('provider', 'local');

    expect($response->json('answer'))->toContain('hal. 3');
});

test('tanpa gemini key fallback ke lokal', function () {
    config(['chatbot.provider' => 'gemini']);
    config(['services.gemini.key' => null]);

    $result = app(ChatbotService::class)->ask('Apa itu energi?', Ebook::first());

    expect($result['provider'])->toBe('local')->and($result['fallback'])->toBeTrue();
});
