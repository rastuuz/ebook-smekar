<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $this->get('/login-admin')->assertOk();
    $this->get('/login-guru')->assertOk();
    $this->get('/login-siswa')->assertOk();
    $this->get('/login')->assertNotFound();
});

test('staff can authenticate with username', function () {
    $user = User::factory()->create([
        'username' => 'admintest',
        'role' => User::ROLE_ADMIN,
    ]);

    $response = $this->post('/login-admin', [
        'username' => 'admintest',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('staff can not authenticate with invalid password', function () {
    User::factory()->create([
        'username' => 'admintest',
        'role' => User::ROLE_ADMIN,
    ]);

    $this->post('/login-admin', [
        'username' => 'admintest',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
