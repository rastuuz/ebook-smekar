<?php

use App\Models\Ebook;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('siswa bisa simpan, lihat simpanan, lalu batal simpan', function () {
    $siswa = User::where('nis', '18034')->first();
    $ebook = Ebook::first();

    $this->actingAs($siswa);

    // Toggle simpan.
    $this->post(route('bookmarks.toggle', $ebook))->assertRedirect();
    expect($siswa->fresh()->hasBookmarked($ebook))->toBeTrue();

    // Halaman simpanan menampilkan judulnya.
    $this->get(route('bookmarks.index'))->assertOk()->assertSee($ebook->title, false);

    // Toggle lagi = batal.
    $this->post(route('bookmarks.toggle', $ebook))->assertRedirect();
    expect($siswa->fresh()->hasBookmarked($ebook))->toBeFalse();
});

test('tamu tidak bisa menyentuh simpanan & riwayat', function () {
    $ebook = Ebook::first();

    $this->get(route('bookmarks.index'))->assertRedirect(route('login.nis'));
    $this->post(route('bookmarks.toggle', $ebook))->assertRedirect(route('login.nis'));
    $this->delete(route('history.clear'))->assertRedirect(route('login.nis'));
});

test('buka detail mencatat riwayat dan tampil di dashboard', function () {
    $siswa = User::where('nis', '18034')->first();
    $ebook = Ebook::first();

    $this->actingAs($siswa);
    $this->get(route('ebooks.show', $ebook))->assertOk();

    $history = $siswa->fresh()->readingHistories()->where('ebook_id', $ebook->id)->first();
    expect($history)->not->toBeNull()->and($history->read_count)->toBe(1);

    // Buka lagi = hitung bertambah.
    $this->get(route('ebooks.show', $ebook))->assertOk();
    expect($history->fresh()->read_count)->toBe(2);

    // Dashboard menampilkan seksi rak pribadi.
    $this->get(route('dashboard'))->assertOk()
        ->assertSee('Terakhir Dibaca', false)
        ->assertSee('Simpanan Saya', false);
});

test('siswa bisa menghapus riwayat', function () {
    $siswa = User::where('nis', '18034')->first();
    $ebook = Ebook::first();

    $this->actingAs($siswa);
    $this->get(route('ebooks.show', $ebook))->assertOk();
    expect($siswa->fresh()->readingHistories()->count())->toBe(1);

    $this->delete(route('history.destroy', $ebook->id))->assertRedirect();
    expect($siswa->fresh()->readingHistories()->count())->toBe(0);

    $this->get(route('ebooks.show', $ebook))->assertOk();
    $this->delete(route('history.clear'))->assertRedirect();
    expect($siswa->fresh()->readingHistories()->count())->toBe(0);
});

test('admin bisa import siswa dari CSV', function () {
    Storage::fake('local');
    $admin = User::where('role', User::ROLE_ADMIN)->first();

    $csv = "nis,name,class\n19001,Uji Coba Satu,XII IPA 1\n19002,Uji Coba Dua,X TKJ 1\nrusak-tanpa-kolom\n";
    $file = UploadedFile::fake()->createWithContent('siswa.csv', $csv);

    $this->actingAs($admin)
        ->post(route('admin.users.import'), ['file' => $file])
        ->assertRedirect(route('admin.users.index', ['role' => User::ROLE_SISWA]));

    expect(User::where('nis', '19001')->first())->not->toBeNull()
        ->and(User::where('nis', '19002')->first()?->name)->toBe('Uji Coba Dua')
        ->and(User::where('nis', '19001')->first()?->role)->toBe(User::ROLE_SISWA);
});

test('import CSV ditolak untuk non-admin dan file bukan CSV', function () {
    $siswa = User::where('nis', '18034')->first();
    $admin = User::where('role', User::ROLE_ADMIN)->first();

    $this->actingAs($siswa)->get(route('admin.users.import-form'))->assertForbidden();

    $bad = UploadedFile::fake()->create('data.pdf', 100, 'application/pdf');
    $this->actingAs($admin)->post(route('admin.users.import'), ['file' => $bad])
        ->assertSessionHasErrors('file');
});

test('admin dashboard memuat statistik baru', function () {
    $this->actingAs(User::where('username', 'admin')->first());
    $this->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Terpopuler', false)
        ->assertSee('Total Guru', false)
        ->assertSee('Total Video', false);
});

test('indeks ulang: tanpa PDF ditolak, non-admin dilarang', function () {
    $ebook = Ebook::first(); // seeder: tanpa file
    expect($ebook->file)->toBeNull();

    $this->actingAs(User::where('username', 'admin')->first())
        ->post(route('admin.ebooks.reindex', $ebook))
        ->assertSessionHas('error');

    $this->actingAs(User::where('nis', '18034')->first())
        ->post(route('admin.ebooks.reindex', $ebook))
        ->assertForbidden();
});
