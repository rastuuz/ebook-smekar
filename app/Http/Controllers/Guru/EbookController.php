<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Admin\EbookController as AdminEbookController;
use App\Http\Controllers\Controller;
use App\Http\Requests\EbookRequest;
use App\Models\Category;
use App\Models\Ebook;
use App\Services\PdfTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Kontribusi guru: hanya tambah e-book (create/store).
 * Edit/hapus tetap admin agar data orang lain aman.
 */
class EbookController extends Controller
{
    /** Batas kontribusi e-book per guru. */
    public const MAX_UPLOADS = 10;

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $used = Ebook::where('created_by', auth()->id())->count();

        return view('guru.ebooks.create', compact('categories', 'used'));
    }

    public function store(EbookRequest $request): RedirectResponse
    {
        $used = Ebook::where('created_by', $request->user()->id)->count();

        if ($used >= self::MAX_UPLOADS) {
            return back()->with('error', 'Batas upload tercapai (10 e-book). Hubungi admin jika ingin menambah kuota.');
        }

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('ebooks/covers', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('ebooks/files', 'local');
        }

        $ebook = Ebook::create($data);

        // Indeks teks PDF agar chatbot langsung bisa memakai e-book ini.
        if ($ebook->file) {
            $result = app(PdfTextExtractor::class)->extract($ebook->file);
            if ($result !== null) {
                $ebook->update([
                    'content_text' => $result['text'],
                    'content_indexed_at' => now(),
                ]);
            }
        }

        AdminEbookController::forgetStatsCache();

        return redirect()->route('dashboard')
            ->with('success', 'Terima kasih! E-book berhasil ditambahkan sebagai kontribusi Anda.');
    }
}
