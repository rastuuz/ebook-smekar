<?php

namespace App\Http\Controllers;

use App\Models\Ebook;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    /**
     * Daftar e-book yang disimpan pengguna.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $bookmarks = $user->bookmarks()
            ->with(['ebook.category'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('bookmarks.index', compact('bookmarks'));
    }

    /**
     * Simpan / batal simpan e-book (toggle).
     */
    public function toggle(Request $request, Ebook $ebook): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $existing = $user->bookmarks()->where('ebook_id', $ebook->id)->first();

        if ($existing) {
            $existing->delete();

            return back()->with('status', 'Dihapus dari simpanan.');
        }

        try {
            $user->bookmarks()->firstOrCreate(['ebook_id' => $ebook->id]);
        } catch (QueryException) {
            // Race condition (double-click): unique [user_id, ebook_id] sudah ada.
            // Anggap sudah tersimpan agar tidak 500.
        }

        return back()->with('status', 'Disimpan ke daftar bacaanmu.');
    }
}
