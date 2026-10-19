<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('admin bisa import guru massal dengan sandi default sama', function () {
    $admin = User::where('username', 'admin')->first();
    $this->actingAs($admin);

    $csv = "username,name\nbudi.santoso,Budi Santoso\nsiti.aisyah,Siti Aisyah\n";
    $tmp = tempnam(sys_get_temp_dir(), 'guru').'.csv';
    file_put_contents($tmp, $csv);
    $file = new UploadedFile($tmp, 'guru.csv', 'text/csv', null, true);

    $this->post(route('admin.users.import'), [
        'role' => 'guru',
        'default_password' => 'guru12345',
        'file' => $file,
    ])->assertRedirect(route('admin.users.index', ['role' => 'guru']));

    $budi = User::where('username', 'budi.santoso')->first();
    $siti = User::where('username', 'siti.aisyah')->first();
    expect($budi)->not->toBeNull();
    expect($siti)->not->toBeNull();
    expect($budi->isGuru())->toBeTrue();
    expect(Hash::check('guru12345', $budi->password))->toBeTrue();

    // Guru bisa login dengan sandi default sama.
    $this->post(route('logout'));
    $this->post(route('login.guru.store'), ['username' => 'budi.santoso', 'password' => 'guru12345'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();

    // Guru ganti sendiri via profil.
    $this->put(route('password.update'), [
        'current_password' => 'guru12345',
        'password' => 'baruku123',
        'password_confirmation' => 'baruku123',
    ])->assertSessionHasNoErrors();
    expect(Hash::check('baruku123', $budi->refresh()->password))->toBeTrue();

    @unlink($tmp);
});

test('import ulang guru tanpa reset tidak menimpa sandi yang sudah diganti', function () {
    $admin = User::where('username', 'admin')->first();
    $this->actingAs($admin);

    $guru = User::where('username', 'guru')->first();
    $guru->update(['password' => Hash::make('sudahdiganti123')]);

    $csv = "username,name\nguru,Guru Update\n";
    $tmp = tempnam(sys_get_temp_dir(), 'guru').'.csv';
    file_put_contents($tmp, $csv);
    $file = new UploadedFile($tmp, 'guru.csv', 'text/csv', null, true);

    $this->post(route('admin.users.import'), [
        'role' => 'guru',
        'default_password' => 'guru12345',
        'file' => $file,
    ])->assertRedirect();

    // Nama terupdate, password tetap.
    expect($guru->refresh()->name)->toBe('Guru Update');
    expect(Hash::check('sudahdiganti123', $guru->refresh()->password))->toBeTrue();
    expect(Hash::check('guru12345', $guru->refresh()->password))->toBeFalse();

    // Dengan centang reset, password kembali ke default.
    $file2 = new UploadedFile($tmp, 'guru.csv', 'text/csv', null, true);
    $this->post(route('admin.users.import'), [
        'role' => 'guru',
        'default_password' => 'guru12345',
        'reset_password' => '1',
        'file' => $file2,
    ])->assertRedirect();
    expect(Hash::check('guru12345', $guru->refresh()->password))->toBeTrue();

    @unlink($tmp);
});

test('import guru menolak username admin dan baris rusak', function () {
    $admin = User::where('username', 'admin')->first();
    $this->actingAs($admin);

    $csv = "username,name\nadmin,Admin Palsu\nburuk!user,Buruk\nvalid.guru,Valid Guru\n,,\n";
    $tmp = tempnam(sys_get_temp_dir(), 'guru').'.csv';
    file_put_contents($tmp, $csv);
    $file = new UploadedFile($tmp, 'guru.csv', 'text/csv', null, true);

    $this->post(route('admin.users.import'), [
        'role' => 'guru',
        'default_password' => 'guru12345',
        'file' => $file,
    ])->assertRedirect();

    expect(User::where('username', 'valid.guru')->exists())->toBeTrue();
    // Admin tidak tertimpa.
    expect(User::where('username', 'admin')->first()->isAdmin())->toBeTrue();
    expect(User::where('username', 'buruk!user')->exists())->toBeFalse();

    @unlink($tmp);
});

test('admin bisa import guru dari Excel dengan header NIP Nama', function () {
    $admin = User::where('username', 'admin')->first();
    $this->actingAs($admin);

    $sheet = new Spreadsheet;
    $ws = $sheet->getActiveSheet();
    $ws->fromArray([['NIP', 'Nama'], ['19800101', 'Guru Excel Satu'], ['19800102', 'Guru Excel Dua']], null, 'A1');
    $tmp = tempnam(sys_get_temp_dir(), 'guru').'.xlsx';
    (new Xlsx($sheet))->save($tmp);
    $file = new UploadedFile($tmp, 'guru.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $this->post(route('admin.users.import'), [
        'role' => 'guru',
        'default_password' => 'guru12345',
        'file' => $file,
    ])->assertRedirect(route('admin.users.index', ['role' => 'guru']));

    expect(User::where('username', '19800101')->first()?->isGuru())->toBeTrue();
    expect(User::where('username', '19800102')->first()?->name)->toBe('Guru Excel Dua');
    expect(Hash::check('guru12345', User::where('username', '19800101')->first()->password))->toBeTrue();

    @unlink($tmp);
});

test('admin bisa import siswa dari Excel urutan kolom bebas + NIS angka', function () {
    $admin = User::where('username', 'admin')->first();
    $this->actingAs($admin);

    $sheet = new Spreadsheet;
    $ws = $sheet->getActiveSheet();
    // Urutan sengaja dibalik + ada kolom No tambahan.
    $ws->fromArray([
        ['No', 'Nama', 'Kelas', 'NIS'],
        [1, 'Siswa Excel Satu', 'XII IPA 1', 19011],
        [2, 'Siswa Excel Dua', 'X TKJ 1', 19012],
    ], null, 'A1');
    $tmp = tempnam(sys_get_temp_dir(), 'siswa').'.xlsx';
    (new Xlsx($sheet))->save($tmp);
    $file = new UploadedFile($tmp, 'siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $this->post(route('admin.users.import'), [
        'role' => 'siswa',
        'file' => $file,
    ])->assertRedirect(route('admin.users.index', ['role' => User::ROLE_SISWA]));

    expect(User::where('nis', '19011')->first()?->name)->toBe('Siswa Excel Satu');
    expect(User::where('nis', '19012')->first()?->class)->toBe('X TKJ 1');

    @unlink($tmp);
});

test('admin bisa import guru Excel format NIP Nama Password', function () {
    $admin = User::where('username', 'admin')->first();
    $this->actingAs($admin);

    $sheet = new Spreadsheet;
    $ws = $sheet->getActiveSheet();
    $ws->fromArray([
        ['NIP', 'Nama', 'Password'],
        ['19700101', 'Guru NIP Satu', 'nip12345'],
        ['19700102', 'Guru NIP Dua', ''],
    ], null, 'A1');
    $tmp = tempnam(sys_get_temp_dir(), 'guru').'.xlsx';
    (new Xlsx($sheet))->save($tmp);
    $file = new UploadedFile($tmp, 'guru.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $this->post(route('admin.users.import'), [
        'role' => 'guru',
        'default_password' => 'guru12345',
        'file' => $file,
    ])->assertRedirect(route('admin.users.index', ['role' => 'guru']));

    // Password per baris dipakai jika terisi, kalau kosong pakai default.
    expect(Hash::check('nip12345', User::where('username', '19700101')->first()->password))->toBeTrue();
    expect(User::where('username', '19700101')->first()?->name)->toBe('Guru NIP Satu');
    expect(Hash::check('guru12345', User::where('username', '19700102')->first()->password))->toBeTrue();

    @unlink($tmp);
});
