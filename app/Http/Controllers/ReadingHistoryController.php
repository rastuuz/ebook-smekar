<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReadingHistoryController extends Controller
{
    /**
     * Hapus seluruh riwayat baca milik sendiri.
     */
    public function clear(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->readingHistories()->delete();

        return back()->with('status', 'Riwayat baca dihapus.');
    }

    /**
     * Hapus satu entri riwayat.
     */
    public function destroy(Request $request, int $ebookId): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->readingHistories()->where('ebook_id', $ebookId)->delete();

        return back()->with('status', 'Entri riwayat dihapus.');
    }
}
