<?php

use App\Models\Ebook;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('halaman video hanya untuk staf, siswa ditolak', function () {
    // Tamu diarahkan ke login NIS.
    $this->get(route('videos.index'))->assertRedirect(route('login.nis'));

    // Siswa ditolak.
    $this->actingAs(User::where('nis', '18034')->first());
    $this->get(route('videos.index'))->assertForbidden();
});

test('guru bisa membuka halaman video dan memfilter', function () {
    $this->actingAs(User::where('username', 'guru')->first());

    $this->get(route('videos.index'))->assertOk();

    $ebook = Ebook::has('videos')->first() ?? Ebook::first();
    $this->get(route('videos.index', ['q' => mb_substr($ebook->title, 0, 4)]))->assertOk();
    $this->get(route('videos.index', ['ebook_id' => $ebook->id]))->assertOk();
});

test('admin bisa membuka halaman video', function () {
    $this->actingAs(User::where('username', 'admin')->first());

    $this->get(route('videos.index'))->assertOk();
});

test('navigasi atas sesuai peran', function () {
    // Siswa: Dashboard + Katalog, tanpa Video/Kelola.
    $this->actingAs(User::where('nis', '18034')->first());
    $this->get(route('dashboard'))->assertSee('Dashboard Siswa', false);
    $this->get(route('dashboard'))->assertDontSee('href="'.route('videos.index').'"', false);
    $this->get(route('dashboard'))->assertDontSee('>Kelola<', false);

    // Guru: Dashboard + Katalog + Video, tanpa Kelola.
    $this->actingAs(User::where('username', 'guru')->first());
    $guruDash = $this->get(route('dashboard'))->assertOk();
    $guruDash->assertSee('Dashboard Guru', false);
    $guruDash->assertDontSee('{{ $user->username }}', false);
    $guruDash->assertSee('href="'.route('videos.index').'"', false);
    $guruDash->assertDontSee('>Kelola<', false);

    // Admin: Kelola + Katalog, tanpa Dashboard ganda.
    $this->actingAs(User::where('username', 'admin')->first());
    $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
});
