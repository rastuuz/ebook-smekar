<?php

// Reset password via email DINONAKTIFKAN: semua user email=null (siswa NIS,
// guru/admin username). Ganti password lewat menu Profil.
// Test ini mengunci keputusan tersebut agar route tidak hidup diam-diam.

test('reset password via email dinonaktifkan', function () {
    $this->get('/forgot-password')->assertNotFound();
    $this->post('/forgot-password', ['email' => 'x@example.com'])->assertNotFound();
    $this->get('/reset-password/token-contoh')->assertNotFound();
    $this->post('/reset-password', [
        'token' => 'token-contoh',
        'email' => 'x@example.com',
        'password' => 'password-baru-123',
        'password_confirmation' => 'password-baru-123',
    ])->assertNotFound();
});
