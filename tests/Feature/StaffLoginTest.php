<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('halaman login admin dan guru terpisah dan bisa dibuka', function () {
    $this->get(route('login.admin'))->assertOk()->assertSee('Masuk sebagai Admin', false);
    $this->get(route('login.guru'))->assertOk()->assertSee('Masuk sebagai Guru', false);
    $this->get('/login')->assertNotFound();
});

test('admin masuk lewat portal admin, ditolak di portal guru', function () {
    $this->post(route('login.admin.store'), ['username' => 'admin', 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs(User::where('username', 'admin')->first());

    $this->post(route('logout'));
    $this->assertGuest();

    $this->post(route('login.guru.store'), ['username' => 'admin', 'password' => 'password'])
        ->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('guru masuk lewat portal guru, ditolak di portal admin', function () {
    $this->post(route('login.guru.store'), ['username' => 'guru', 'password' => 'password'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs(User::where('username', 'guru')->first());

    $this->post(route('logout'));
    $this->assertGuest();

    $this->post(route('login.admin.store'), ['username' => 'guru', 'password' => 'password'])
        ->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('admin bisa membuat akun guru, guru read-only tanpa hak kelola', function () {
    $this->actingAs(User::where('username', 'admin')->first());

    $this->post(route('admin.users.store'), [
        'role' => 'guru',
        'name' => 'Guru Baru',
        'username' => 'gurubaru',
        'password' => 'rahasia123',
    ])->assertRedirect(route('admin.users.index', ['role' => 'guru']));

    $guru = User::where('username', 'gurubaru')->first();
    expect($guru)->not->toBeNull();
    expect($guru->isGuru())->toBeTrue();

    $this->post(route('logout'));

    $this->post(route('login.guru.store'), ['username' => 'gurubaru', 'password' => 'rahasia123'])
        ->assertRedirect(route('dashboard'));

    // Guru hanya membaca: katalog + video boleh, area kelola ditolak.
    $this->get(route('ebooks.index'))->assertOk();
    $this->get(route('videos.index'))->assertOk();
    $this->get(route('admin.dashboard'))->assertForbidden();
    $this->get(route('admin.ebooks.index'))->assertForbidden();
    $this->get(route('admin.users.index'))->assertForbidden();
});

test('admin tidak bisa membuat akun role admin dari CRUD', function () {
    $this->actingAs(User::where('username', 'admin')->first());

    $this->post(route('admin.users.store'), [
        'role' => 'admin',
        'name' => 'Admin Liar',
        'username' => 'adminliar',
        'password' => 'rahasia123',
    ])->assertSessionHasErrors('role');

    expect(User::where('username', 'adminliar')->exists())->toBeFalse();
});

test('siswa tanpa username tidak bisa masuk portal guru', function () {
    $this->post(route('login.guru.store'), ['username' => '18034', 'password' => 'password'])
        ->assertSessionHasErrors('username');
    $this->assertGuest();
});

test('admin bisa ganti username dan password dari halaman profil', function () {
    $this->actingAs($admin = User::where('username', 'admin')->first());

    $this->patch(route('profile.update'), ['name' => 'Administrator', 'username' => 'adminbaru'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect($admin->refresh()->username)->toBe('adminbaru');

    $this->post(route('logout'));

    // Username lama sudah tidak berlaku, yang baru bisa dipakai.
    $this->post(route('login.admin.store'), ['username' => 'admin', 'password' => 'password'])
        ->assertSessionHasErrors('username');
    $this->post(route('login.admin.store'), ['username' => 'adminbaru', 'password' => 'password'])
        ->assertRedirect(route('admin.dashboard'));

    // Ganti password lalu login dengan password baru.
    $this->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'baru12345',
        'password_confirmation' => 'baru12345',
    ])->assertSessionHasNoErrors();

    $this->post(route('logout'));

    $this->post(route('login.admin.store'), ['username' => 'adminbaru', 'password' => 'baru12345'])
        ->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticated();
});

test('guru bisa ganti username dan password dari halaman profil', function () {
    $this->actingAs($guru = User::where('username', 'guru')->first());

    $this->patch(route('profile.update'), ['name' => 'Guru', 'username' => 'gurubaru'])
        ->assertSessionHasNoErrors();

    expect($guru->refresh()->username)->toBe('gurubaru');

    $this->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'baru12345',
        'password_confirmation' => 'baru12345',
    ])->assertSessionHasNoErrors();

    $this->post(route('logout'));

    $this->post(route('login.guru.store'), ['username' => 'gurubaru', 'password' => 'baru12345'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
});

test('username staf harus unik dan siswa tidak boleh mengisinya', function () {
    $this->actingAs(User::where('username', 'admin')->first());

    // Pakai username milik guru ? ditolak.
    $this->patch(route('profile.update'), ['name' => 'Administrator', 'username' => 'guru'])
        ->assertSessionHasErrors('username');

    // Siswa mengirim username ? ditolak.
    $this->actingAs(User::where('nis', '18034')->first());
    $this->patch(route('profile.update'), ['name' => 'Budi', 'username' => 'budi'])
        ->assertSessionHasErrors('username');
});

test('admin dan guru bisa membuka halaman profil langsung', function () {
    $this->actingAs(User::where('username', 'admin')->first());
    $this->get(route('profile.edit'))->assertOk();
    // Sidebar admin memuat jalan pintas Profil Saya.
    $this->get(route('admin.dashboard'))->assertSee('Profil Saya', false);

    $this->actingAs(User::where('username', 'guru')->first());
    $this->get(route('profile.edit'))->assertOk();
    // Dashboard guru memuat kartu Akun Saya.
    $this->get(route('dashboard'))->assertSee('Akun Saya', false);
});
