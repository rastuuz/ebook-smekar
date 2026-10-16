<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\EbookController as AdminEbookController;
use App\Http\Controllers\Admin\EbookVideoController as AdminVideoController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\EbookController as SiswaEbookController;
use App\Http\Controllers\Guru\EbookController as GuruEbookController;
use App\Http\Controllers\Guru\EbookVideoController as GuruVideoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReadingHistoryController;
use App\Http\Controllers\VideoController;
use App\Models\Category;
use App\Models\Ebook;
use App\Models\EbookVideo;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $ebooks = Ebook::with('category')->latest()->take(6)->get();
    $categories = Category::withCount('ebooks')->orderBy('name')->get();
    // Cache 60 detik agar halaman publik tetap ringan.
    $totalEbooks = Cache::remember('stats:total-ebooks', 60, fn () => Ebook::count());
    $totalCategories = Cache::remember('stats:total-categories', 60, fn () => Category::count());

    return view('welcome', compact('ebooks', 'categories', 'totalEbooks', 'totalCategories'));
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Dashboard sesuai peran: admin ke kelola, guru ke ruang guru, siswa ke ruang baca.
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isGuru()) {
            return view('guru.dashboard', [
                'user' => $user,
                'ebooks' => Ebook::with(['category', 'contributor'])->latest()->take(6)->get(),
                'totalEbooks' => Cache::remember('stats:total-ebooks', 60, fn () => Ebook::count()),
                'totalCategories' => Cache::remember('stats:total-categories', 60, fn () => Category::count()),
                'totalVideos' => Cache::remember('stats:total-videos', 60, fn () => EbookVideo::count()),
                'myEbooks' => Ebook::with('category')->where('created_by', $user->id)->latest()->take(5)->get(),
                'myVideos' => EbookVideo::with('ebook')->where('created_by', $user->id)->latest()->take(5)->get(),
                'myEbooksCount' => Ebook::where('created_by', $user->id)->count(),
                'myVideosCount' => EbookVideo::where('created_by', $user->id)->count(),
            ]);
        }

        $ebooks = Ebook::with('category')->latest()->take(6)->get();
        $categories = Category::withCount('ebooks')->orderBy('name')->get();

        // Rak pribadi siswa: simpanan + terakhir dibaca.
        $savedEbooks = $user->savedEbooks()->with('category')->latest('bookmarks.created_at')->take(6)->get();
        $recentReads = $user->readingHistories()->with('ebook.category')->latest('last_read_at')->take(6)->get();

        return view('dashboard', [
            'user' => $user,
            'ebooks' => $ebooks,
            'categories' => $categories,
            'savedEbooks' => $savedEbooks,
            'recentReads' => $recentReads,
            'totalEbooks' => Cache::remember('stats:total-ebooks', 60, fn () => Ebook::count()),
            'totalCategories' => Cache::remember('stats:total-categories', 60, fn () => Category::count()),
        ]);
    })->name('dashboard');

    // Area baca: katalog + detail + baca PDF (siswa, guru, admin).
    Route::get('/ebooks', [SiswaEbookController::class, 'index'])->name('ebooks.index');
    Route::get('/ebooks/{ebook}', [SiswaEbookController::class, 'show'])->name('ebooks.show');
    Route::get('/ebooks/{ebook}/read', [SiswaEbookController::class, 'read'])->name('ebooks.read');

    // Daftar video pembelajaran (read-only) khusus staf: guru & admin.
    Route::get('/videos', [VideoController::class, 'index'])
        ->middleware('staff')
        ->name('videos.index');

    // Kontribusi guru: hanya tambah e-book & video (edit/hapus tetap admin).
    Route::middleware(['staff'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/ebooks/create', [GuruEbookController::class, 'create'])->name('ebooks.create');
        Route::post('/ebooks', [GuruEbookController::class, 'store'])->name('ebooks.store');
        Route::get('/videos/create', [GuruVideoController::class, 'create'])->name('videos.create');
        Route::post('/videos', [GuruVideoController::class, 'store'])->name('videos.store');
    });

    // Simpanan (bookmark) + riwayat baca milik sendiri.
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/{ebook}/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
    Route::delete('/history', [ReadingHistoryController::class, 'clear'])->name('history.clear');
    Route::delete('/history/{ebookId}', [ReadingHistoryController::class, 'destroy'])
        ->whereNumber('ebookId')->name('history.destroy');

    // Chatbot pembelajaran (throttle agar tidak spam)
    Route::post('/chatbot/ask', [ChatbotController::class, 'ask'])
        ->middleware('throttle:20,1')
        ->name('chatbot.ask');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Area kelola khusus admin: konten + data pengguna.
// Guru tidak punya hak ubah apa pun.
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        // Ringkasan kelola: cache 60 detik agar tetap ringan.
        $stats = Cache::remember('stats:admin-dashboard', 60, function () {
            return [
                'totalEbooks' => Ebook::count(),
                'totalCategories' => Category::count(),
                'totalSiswa' => User::where('role', 'siswa')->count(),
                'totalGuru' => User::where('role', 'guru')->count(),
                'totalVideos' => EbookVideo::count(),
                'missingPdf' => Ebook::whereNull('file')->count(),
                'notIndexed' => Ebook::whereNotNull('file')->whereNull('content_text')->count(),
            ];
        });

        // E-book yang perlu perhatian: tanpa PDF atau teks tak terbaca.
        $needsAttention = Ebook::with('category')
            ->where(fn ($q) => $q->whereNull('file')->orWhereNull('content_text'))
            ->latest()->take(5)->get();

        // Terpopuler berdasar simpanan + riwayat baca.
        // Pakai alias withCount agar tidak hitung ganda via subquery.
        $popular = Ebook::with('category')
            ->withCount(['bookmarks', 'readingHistories'])
            ->orderByRaw('bookmarks_count + reading_histories_count DESC')
            ->take(5)->get();

        return view('admin.dashboard', array_merge($stats, [
            'latestEbooks' => Ebook::with('category')->latest()->take(5)->get(),
            'needsAttention' => $needsAttention,
            'popular' => $popular,
        ]));
    })->name('dashboard');

    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('ebooks', AdminEbookController::class)->except(['show']);
    Route::post('ebooks/{ebook}/reindex', [AdminEbookController::class, 'reindex'])->name('ebooks.reindex');
    Route::resource('videos', AdminVideoController::class)
        ->parameters(['videos' => 'video'])
        ->except(['show']);

    Route::get('users/import', [AdminUserController::class, 'importForm'])->name('users.import-form');
    Route::post('users/import', [AdminUserController::class, 'import'])->name('users.import');
    Route::resource('users', AdminUserController::class)->except(['show']);
});

require __DIR__.'/auth.php';
