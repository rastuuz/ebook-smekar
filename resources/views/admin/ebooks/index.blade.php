@extends('layouts.admin')
@section('title', 'E-Book')

@section('content')
<form method="GET" action="{{ route('admin.ebooks.index') }}" class="card mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
    <input type="search" name="q" value="{{ $search ?? '' }}" autocomplete="off" placeholder="Cari judul / penulis…" class="input w-full !mt-0 sm:max-w-xs" aria-label="Cari e-book">
    <p class="text-sm text-stone-500 sm:ml-1">{{ $ebooks->total() }} e-book • cover maks 2MB • PDF maks 30MB</p>
    <div class="flex gap-2 sm:ml-auto">
        @if(! empty($search ?? ''))
            <a href="{{ route('admin.ebooks.index') }}" class="btn-ghost !py-2 text-sm">Atur ulang</a>
        @endif
        <a href="{{ route('admin.ebooks.create') }}" class="btn-primary !py-2 text-center text-sm">+ Tambah E-Book</a>
    </div>
</form>

@if($ebooks->isEmpty())
    <div class="card card-pad py-12 text-center text-sm text-stone-500">
        @if(! empty($search ?? ''))
            Tidak ada hasil untuk “{{ $search }}”. <a href="{{ route('admin.ebooks.index') }}" class="font-semibold underline underline-offset-4">Tampilkan semua</a>.
        @else
            Belum ada e-book. Upload PDF pertama Anda.
        @endif
    </div>
@else
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        @foreach($ebooks as $ebook)
        <div class="card flex gap-4 p-4">
            <div class="flex h-24 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-stone-100 text-xs text-stone-400">
                @if($ebook->cover)
                    <img src="{{ $ebook->cover_url }}" alt="cover" class="h-full w-full object-cover" loading="lazy" decoding="async">
                @else
                    <span class="flex h-full w-full items-center justify-center bg-[#f4f1ea] font-display text-2xl font-semibold text-stone-300" aria-hidden="true">{{ strtoupper(mb_substr($ebook->title, 0, 1)) }}</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-brand-800">{{ $ebook->category->name ?? '-' }} <span class="font-normal text-stone-400">• {{ $ebook->videos_count ?? $ebook->videos->count() }} video</span></p>
                <p class="mt-0.5 font-semibold line-clamp-1">{{ $ebook->title }}</p>
                <p class="text-xs text-stone-400">{{ $ebook->author }}</p>
                <p class="mt-1.5">
                    @if($ebook->indexStatus() === 'ready')
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Siap chatbot</span>
                    @elseif($ebook->indexStatus() === 'needs-attention')
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/25">Teks tak terbaca (scan?)</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-stone-100 px-2 py-0.5 text-[11px] font-semibold text-stone-500 ring-1 ring-inset ring-stone-500/10">PDF belum ada</span>
                    @endif
                </p>
                <div class="mt-2.5 flex flex-wrap gap-1.5">
                    <a href="{{ route('admin.ebooks.edit', $ebook) }}" class="btn-row">Edit</a>
                    @if($ebook->file)
                        <form method="POST" action="{{ route('admin.ebooks.reindex', $ebook) }}">
                            @csrf
                            <button class="btn-row" title="Ekstrak ulang teks PDF untuk chatbot">Indeks ulang</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.videos.create', ['ebook_id' => $ebook->id]) }}" class="btn-row">+ Video</a>
                    <form method="POST" action="{{ route('admin.ebooks.destroy', $ebook) }}" onsubmit="return confirm('Hapus e-book + file + video?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $ebooks->links() }}</div>
@endif
@endsection
