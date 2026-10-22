<x-app-layout>
    <x-slot name="title">Video Pembelajaran</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Materi Pendukung</p>
                <h2 class="title-display mt-2 text-2xl sm:text-[1.7rem]">Video Pembelajaran</h2>
                <p class="mt-1 max-w-xl text-sm text-stone-500">Semua video materi e-book dalam satu halaman.</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-ghost inline-flex items-center gap-1.5 !py-2 !text-xs">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                        Panel Admin
                    </a>
                @elseif(auth()->user()?->isGuru())
                    <a href="{{ route('dashboard') }}" class="btn-ghost inline-flex items-center gap-1.5 !py-2 !text-xs">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
                        Dashboard
                    </a>
                @endif
                <a href="{{ route('guru.videos.create') }}" class="btn-gold !py-2 !text-xs">+ Tambah Video</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- Filter: otomatis terkirim saat diketik/diganti --}}
            <form id="video-filter" method="GET" action="{{ route('videos.index') }}" class="card shadow-soft">
                <div class="flex items-center gap-3 border-b border-stone-200/70 px-4 py-3 transition focus-within:border-[#c9a227]/60 focus-within:bg-[#faf3dd]/40 sm:px-5">
                    <svg class="h-5 w-5 shrink-0 text-stone-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14Zm0 2a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z"/><path d="M15.8 15.1l4.6 4.6-1.5 1.5-4.6-4.6 1.5-1.5Z"/></svg>
                    <input id="video-q" type="search" name="q" value="{{ $search }}" autocomplete="off" aria-label="Cari video"
                        placeholder="Ketik judul video atau e-book&hellip;"
                        class="w-full min-w-0 border-0 bg-transparent p-0 text-base text-stone-900 placeholder:text-stone-400 focus:ring-0 sm:text-[15px]">
                </div>
                <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:px-5">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">E-Book</span>
                    <select id="video-ebook" name="ebook_id" class="input w-full !mt-0 sm:max-w-xs">
                        <option value="">Semua e-book</option>
                        @foreach($ebooks as $ebook)
                            <option value="{{ $ebook->id }}" @selected((string)$ebookId === (string)$ebook->id)>{{ $ebook->title }}</option>
                        @endforeach
                    </select>
                    <p class="sm:ml-auto text-xs text-stone-500" aria-live="polite">{{ $videos->total() }} video ditemukan.</p>
                </div>
            </form>

            @if($videos->isEmpty())
                <div class="card card-pad py-12 text-center">
                    <p class="title-display text-lg">Tidak ada video ditemukan</p>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">Coba kata kunci lain atau ubah filter e-book.</p>
                    <a href="{{ route('videos.index') }}" class="btn-ghost mt-4">Tampilkan semua</a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach($videos as $video)
                        <article class="card overflow-hidden">
                            @if($video->embed_url)
                                <div class="aspect-video w-full bg-stone-900">
                                    <iframe src="{{ $video->embed_url }}" class="h-full w-full"
                                        title="{{ $video->title }}" loading="lazy"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                </div>
                            @endif
                            <div class="p-4 sm:p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Dari e-book</p>
                                    @if(auth()->user()?->isStaff())
                                        <a href="{{ route('guru.videos.create', ['ebook_id' => $video->ebook_id]) }}" class="text-[11px] font-semibold text-brand-800 underline-offset-4 hover:underline">+ Tambah video</a>
                                    @endif
                                </div>
                                <h3 class="title-display mt-1 line-clamp-2 text-lg leading-snug">{{ $video->title }}</h3>
                                @if($video->contributor)
                                    <p class="mt-1 text-xs text-stone-400">Kontribusi {{ $video->contributor->name }}</p>
                                @endif
                                @if($video->ebook)
                                    <a href="{{ route('ebooks.show', $video->ebook) }}" class="mt-0.5 inline-block truncate text-sm font-medium text-brand-800 underline-offset-4 hover:underline">{{ $video->ebook->title }}</a>
                                @endif
                                @if($video->description)
                                    <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-stone-500">{{ $video->description }}</p>
                                @endif
                                <a href="{{ $video->youtube_url }}" target="_blank" rel="noopener" class="mt-2 inline-block text-xs font-semibold text-brand-800 underline-offset-4 hover:underline">Buka di YouTube</a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-4 overflow-x-auto">{{ $videos->links() }}</div>
            @endif
        </div>
    </div>

<script>
(function () {
    const form = document.getElementById('video-filter');
    const q = document.getElementById('video-q');
    const ebook = document.getElementById('video-ebook');
    if (!form || !q) return;
    let timer = null;
    // Tanpa tombol Cari: filter terkirim otomatis. Tanpa JS, Enter tetap mengirim form.
    q.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.submit(), 500);
    });
    if (ebook) ebook.addEventListener('change', () => form.submit());
})();
</script>
</x-app-layout>
