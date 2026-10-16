<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Ebook;
use App\Models\ReadingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class EbookController extends Controller
{
    public function index(): View|JsonResponse
    {
        $query = Ebook::with(['category', 'videos'])
            ->withCount('videos');

        $search = trim(mb_substr((string) request('q', ''), 0, 100));
        $categoryId = request('category_id');
        // Hanya terima category_id numerik agar filter tak valid diabaikan dengan aman.
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;

        if ($search !== '') {
            $query->search($search);
        }

        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // Live search: kembalikan JSON ringan tanpa reload halaman.
        // take(12) disamakan dengan paginasi agar jumlah kartu konsisten.
        if (request()->boolean('ajax') || request()->wantsJson()) {
            $total = (clone $query)->count();
            $ebooks = $query->latest()->take(12)->get();

            return response()->json([
                'total' => $total,
                'search' => $search,
                'data' => $ebooks->map(fn (Ebook $ebook) => [
                    'id' => $ebook->id,
                    'title' => $ebook->title,
                    'author' => $ebook->author,
                    'description' => $ebook->description,
                    'category' => $ebook->category->name ?? '-',
                    'cover_url' => $ebook->cover_url,
                    'videos_count' => $ebook->videos_count ?? $ebook->videos->count(),
                    'detail_url' => route('ebooks.show', $ebook),
                ])->values(),
            ]);
        }

        $ebooks = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::withCount('ebooks')->orderBy('name')->get();

        return view('ebooks.index', compact('ebooks', 'categories', 'search', 'categoryId'));
    }

    public function show(Ebook $ebook): View
    {
        $ebook->load(['category', 'contributor', 'videos.contributor']);

        $this->recordReading($ebook);

        $related = Ebook::with('category')
            ->where('category_id', $ebook->category_id)
            ->where('id', '!=', $ebook->id)
            ->latest()
            ->take(4)
            ->get();

        $userId = Auth::id();
        $isBookmarked = $userId !== null
            && Bookmark::where('user_id', $userId)->where('ebook_id', $ebook->id)->exists();

        return view('ebooks.show', compact('ebook', 'related', 'isBookmarked'));
    }

    public function read(Ebook $ebook): BinaryFileResponse|Response
    {
        if (! $ebook->file) {
            abort(404, 'File PDF belum tersedia untuk e-book ini.');
        }

        // File baru di disk private, file lama (fallback) masih di public.
        $disk = Storage::disk('local')->exists($ebook->file) ? 'local' : 'public';

        if (! Storage::disk($disk)->exists($ebook->file)) {
            abort(404, 'File PDF belum tersedia untuk e-book ini.');
        }

        $this->recordReading($ebook);

        $path = Storage::disk($disk)->path($ebook->file);
        $safeName = str_replace(['"', "\r", "\n"], '', basename($path));

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    /**
     * Catat riwayat baca: buat sekali per (pengguna, e-book),
     * lalu perbarui waktu + hitung kunjungan. Gagal catat tak boleh
     * menggagalkan halaman baca.
     */
    protected function recordReading(Ebook $ebook): void
    {
        try {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            $history = ReadingHistory::firstOrNew([
                'user_id' => $userId,
                'ebook_id' => $ebook->id,
            ]);

            if (! $history->exists) {
                $history->read_count = 1;
            } else {
                $history->read_count = ($history->read_count ?? 0) + 1;
            }

            $history->last_read_at = now();
            $history->save();
        } catch (\Throwable) {
            // Abaikan: riwayat bersifat pelengkap.
        }
    }
}
