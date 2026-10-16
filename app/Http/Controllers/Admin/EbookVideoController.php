<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EbookVideoRequest;
use App\Models\Ebook;
use App\Models\EbookVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class EbookVideoController extends Controller
{
    public function index(): View
    {
        $videos = EbookVideo::with(['ebook.category', 'contributor'])
            ->latest()
            ->paginate(12);

        return view('admin.videos.index', compact('videos'));
    }

    public function create(): View
    {
        $ebooks = Ebook::with('category')->orderBy('title')->get();
        $selectedEbookId = request('ebook_id');

        return view('admin.videos.create', compact('ebooks', 'selectedEbookId'));
    }

    public function store(EbookVideoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        EbookVideo::create($data);

        Cache::forget('stats:total-videos');
        Cache::forget('stats:admin-dashboard');

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video berhasil ditambahkan.');
    }

    public function edit(EbookVideo $video): View
    {
        $ebooks = Ebook::orderBy('title')->get();

        return view('admin.videos.edit', compact('video', 'ebooks'));
    }

    public function update(EbookVideoRequest $request, EbookVideo $video): RedirectResponse
    {
        $video->update($request->validated());

        Cache::forget('stats:total-videos');
        Cache::forget('stats:admin-dashboard');

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video berhasil diperbarui.');
    }

    public function destroy(EbookVideo $video): RedirectResponse
    {
        $video->delete();

        Cache::forget('stats:total-videos');
        Cache::forget('stats:admin-dashboard');

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video berhasil dihapus.');
    }
}
