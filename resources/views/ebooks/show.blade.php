<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('ebooks.index') }}" class="btn-ghost shrink-0 !px-3 !py-1.5 !text-xs">← Katalog</a>
            <h2 class="title-display min-w-0 flex-1 truncate text-xl">{{ $ebook->title }}</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- Detail utama --}}
            <div class="card">
                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:p-6">
                    <div class="book-frame mx-auto h-60 w-44 sm:mx-0">
                        @if($ebook->cover)
                            <img src="{{ $ebook->cover_url }}" alt="Sampul {{ $ebook->title }}" class="h-full w-full object-cover" fetchpriority="high" decoding="async">
                        @else
                            <span class="font-display text-7xl font-semibold text-stone-300" aria-hidden="true">{{ strtoupper(mb_substr($ebook->title, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="badge-cat">{{ $ebook->category->name ?? '-' }}</span>
                        <h1 class="title-display mt-2 text-2xl leading-tight tracking-tight sm:text-3xl">{{ $ebook->title }}</h1>
                        <p class="mt-1 text-sm text-stone-500">Oleh <span class="font-semibold text-stone-800">{{ $ebook->author }}</span></p>
                        @if($ebook->contributor)
                            <p class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-200">✨ Dikontribusikan oleh {{ $ebook->contributor->name }}</p>
                        @endif
                        @if($ebook->description)
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-stone-600">{{ $ebook->description }}</p>
                        @endif
                        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                            @if($ebook->file)
                                <a href="{{ route('ebooks.read', $ebook) }}" target="_blank" class="btn-gold">Baca E-Book →</a>
                            @else
                                <span class="inline-flex items-center justify-center rounded-xl border border-dashed border-stone-300 bg-stone-50 px-5 py-2.5 text-sm text-stone-400">PDF belum tersedia</span>
                            @endif
                            @if($ebook->videos->isNotEmpty())
                                <a href="#videos" class="btn-ghost">{{ $ebook->videos->count() }} Video</a>
                            @endif
                            <form method="POST" action="{{ route('bookmarks.toggle', $ebook) }}" class="sm:inline">
                                @csrf
                                <button class="btn-ghost w-full sm:w-auto" title="{{ ($isBookmarked ?? false) ? 'Hapus dari simpanan' : 'Simpan ke rak pribadimu' }}">
                                    {{ ($isBookmarked ?? false) ? '★ Tersimpan' : '☆ Simpan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if($ebook->file)
                <div class="border-t border-stone-200/80 p-4 sm:p-5">
                    <p class="title-display mb-2 text-base">Pratinjau PDF</p>
                    <div class="h-[60dvh] max-h-[480px] min-h-[320px] overflow-hidden rounded-lg border border-stone-200">
                        <iframe src="{{ route('ebooks.read', $ebook) }}" class="h-full w-full" title="Pratinjau PDF"></iframe>
                    </div>
                    <p class="mt-2 text-xs text-stone-400">Jika PDF tidak tampil, <a class="font-semibold text-brand-800 underline underline-offset-4" target="_blank" href="{{ route('ebooks.read', $ebook) }}">buka di tab baru</a>.</p>
                </div>
                @endif
            </div>

            {{-- Video --}}
            <div id="videos" class="card card-pad scroll-mt-24">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-400">Materi Pendukung</p>
                <h2 class="title-display mt-1 text-lg leading-tight">Video Pembelajaran</h2>
                @if($ebook->videos->isEmpty())
                    <p class="mt-3 rounded-lg bg-stone-50 p-4 text-sm text-stone-500">Belum ada video untuk e-book ini.</p>
                @else
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @foreach($ebook->videos as $video)
                            <div class="overflow-hidden rounded-xl border border-stone-200 transition hover:shadow-soft">
                                @if($video->embed_url)
                                    <div class="aspect-video w-full bg-stone-900">
                                        <iframe src="{{ $video->embed_url }}" class="h-full w-full"
                                            title="{{ $video->title }}" loading="lazy"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                    </div>
                                @endif
                                <div class="p-3.5">
                                    <p class="text-sm font-semibold text-stone-900">{{ $video->title }}</p>
                                    @if($video->contributor)
                                        <p class="mt-1 text-xs text-stone-400">✨ Kontribusi {{ $video->contributor->name }}</p>
                                    @endif
                                    @if($video->description)<p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-stone-500">{{ $video->description }}</p>@endif
                                    <a href="{{ $video->youtube_url }}" target="_blank" rel="noopener" class="mt-1.5 inline-block text-xs font-semibold text-brand-800 underline-offset-4 hover:underline">Buka di YouTube</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Chatbot inline --}}
            @include('chatbot._widget', ['ebook' => $ebook])

            {{-- Terkait --}}
            @if($related->isNotEmpty())
            <div>
                <h2 class="title-display mb-3 text-lg">E-Book Terkait</h2>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($related as $item)
                        <a href="{{ route('ebooks.show', $item) }}" class="card card-pad !p-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-lift">
                            <span class="badge-cat">{{ $item->category->name ?? '-' }}</span>
                            <p class="mt-1.5 font-semibold line-clamp-1 text-stone-900">{{ $item->title }}</p>
                            <p class="text-xs text-stone-500">{{ $item->author }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        window.__EBOOK__ = { id: "{{ $ebook->id }}", title: @json($ebook->title) };
    </script>
</x-app-layout>
