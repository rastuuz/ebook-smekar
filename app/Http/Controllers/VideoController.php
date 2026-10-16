<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Models\EbookVideo;
use App\Support\Like;
use Illuminate\View\View;

class VideoController extends Controller
{
    /**
     * Daftar video pembelajaran (read-only) untuk guru & admin.
     */
    public function index(): View
    {
        $query = EbookVideo::with(['ebook.category', 'contributor']);

        $search = trim(mb_substr((string) request('q', ''), 0, 100));
        $ebookId = request('ebook_id');
        $ebookId = is_numeric($ebookId) ? (int) $ebookId : null;

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                Like::whereLike($q, 'title', $search);
                Like::orWhereLike($q, 'description', $search);
                $q->orWhereHas('ebook', function ($eq) use ($search) {
                    Like::whereLike($eq, 'title', $search);
                });
            });
        }

        if (! empty($ebookId)) {
            $query->where('ebook_id', $ebookId);
        }

        $videos = $query->latest()->paginate(9)->withQueryString();
        $ebooks = Ebook::orderBy('title')->get(['id', 'title']);

        return view('videos.index', compact('videos', 'ebooks', 'search', 'ebookId'));
    }
}
