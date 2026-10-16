<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Admin\EbookVideoController as AdminVideoController;
use App\Http\Controllers\Controller;
use App\Http\Requests\EbookVideoRequest;
use App\Models\Ebook;
use App\Models\EbookVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Kontribusi guru: hanya tambah video YouTube (create/store).
 * Edit/hapus tetap admin agar data orang lain aman.
 */
class EbookVideoController extends Controller
{
    /** Batas kontribusi video per guru. */
    public const MAX_UPLOADS = 10;

    public function create(): View
    {
        $ebooks = Ebook::with('category')->orderBy('title')->get();
        $selectedEbookId = request('ebook_id');
        $used = EbookVideo::where('created_by', auth()->id())->count();

        return view('guru.videos.create', compact('ebooks', 'selectedEbookId', 'used'));
    }

    public function store(EbookVideoRequest $request): RedirectResponse
    {
        $used = EbookVideo::where('created_by', $request->user()->id)->count();

        if ($used >= self::MAX_UPLOADS) {
            return back()->with('error', 'Batas upload tercapai (10 video). Hubungi admin jika ingin menambah kuota.');
        }

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        EbookVideo::create($data);

        Cache::forget('stats:total-videos');
        Cache::forget('stats:admin-dashboard');

        return redirect()->route('videos.index')
            ->with('success', 'Terima kasih! Video berhasil ditambahkan sebagai kontribusi Anda.');
    }
}
