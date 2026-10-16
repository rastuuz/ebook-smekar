<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EbookRequest;
use App\Models\Category;
use App\Models\Ebook;
use App\Services\PdfTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EbookController extends Controller
{
    public function index(): View
    {
        $search = trim(mb_substr((string) request('q', ''), 0, 100));

        $query = Ebook::with(['category', 'videos', 'contributor'])
            ->withCount('videos');

        if ($search !== '') {
            $query->search($search);
        }

        $ebooks = $query->latest()->paginate(12)->withQueryString();

        return view('admin.ebooks.index', compact('ebooks', 'search'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.ebooks.create', compact('categories'));
    }

    public function store(EbookRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('ebooks/covers', 'public');
        }

        if ($request->hasFile('file')) {
            // PDF disimpan di disk private agar tak bisa dibuka via /storage langsung.
            $data['file'] = $request->file('file')->store('ebooks/files', 'local');
        }

        $ebook = Ebook::create($data);

        $this->indexPdfContent($ebook);

        self::forgetStatsCache();

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'E-book berhasil ditambahkan.');
    }

    public function edit(Ebook $ebook): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.ebooks.edit', compact('ebook', 'categories'));
    }

    public function update(EbookRequest $request, Ebook $ebook): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            if ($ebook->cover) {
                Storage::disk('public')->delete($ebook->cover);
            }
            $data['cover'] = $request->file('cover')->store('ebooks/covers', 'public');
        } else {
            unset($data['cover']);
        }

        if ($request->hasFile('file')) {
            if ($ebook->file) {
                Storage::disk('local')->delete($ebook->file);
                Storage::disk('public')->delete($ebook->file); // bersih-bersih file lama
            }
            $data['file'] = $request->file('file')->store('ebooks/files', 'local');
            $data['content_text'] = null;
            $data['content_indexed_at'] = null;
        } else {
            unset($data['file']);
        }

        $ebook->update($data);

        if ($request->hasFile('file')) {
            $this->indexPdfContent($ebook->fresh());
        }

        self::forgetStatsCache();

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'E-book berhasil diperbarui.');
    }

    public function destroy(Ebook $ebook): RedirectResponse
    {
        if ($ebook->cover) {
            Storage::disk('public')->delete($ebook->cover);
        }

        if ($ebook->file) {
            Storage::disk('local')->delete($ebook->file);
            Storage::disk('public')->delete($ebook->file); // fallback file lama
        }

        $ebook->delete(); // videos ikut terhapus via cascade

        self::forgetStatsCache();

        return redirect()->route('admin.ebooks.index')
            ->with('success', 'E-book berhasil dihapus.');
    }

    /**
     * Indeks ulang teks PDF (misal setelah upload scan / gagal ekstrak).
     */
    public function reindex(Ebook $ebook): RedirectResponse
    {
        if (! $ebook->file) {
            return back()->with('error', 'E-book ini belum punya file PDF.');
        }

        $result = app(PdfTextExtractor::class)->extract($ebook->file);

        if ($result === null) {
            return back()->with('error', 'Teks PDF tidak terbaca (kemungkinan hasil scan/gambar). Chatbot tetap bisa menjawab dari judul & deskripsi.');
        }

        $ebook->update([
            'content_text' => $result['text'],
            'content_indexed_at' => now(),
        ]);

        return back()->with('success', 'Indeks chatbot berhasil diperbarui.');
    }

    /**
     * Cache statistik yang dipakai home + dashboard + admin.
     */
    public static function forgetStatsCache(): void
    {
        Cache::forget('stats:total-ebooks');
        Cache::forget('stats:total-categories');
        Cache::forget('stats:total-videos');
        Cache::forget('stats:admin-dashboard');
    }

    /**
     * Ekstrak teks PDF untuk chatbot. Kegagalan tidak menggagalkan request.
     */
    protected function indexPdfContent(Ebook $ebook): void
    {
        if (! $ebook->file) {
            return;
        }

        $result = app(PdfTextExtractor::class)->extract($ebook->file);

        if ($result === null) {
            return;
        }

        $ebook->update([
            'content_text' => $result['text'],
            'content_indexed_at' => now(),
        ]);
    }
}
