<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Ruang Baca</p>
                <h2 class="title-display mt-2 text-2xl sm:text-3xl">Dashboard Siswa</h2>
            </div>
            <a href="{{ route('ebooks.index') }}" class="btn-gold shrink-0 !px-4 !py-2 !text-xs">Buka Katalog →</a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Profil siswa --}}
            <div class="card-dark">
                <div class="relative flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-b from-[#e3c964] to-[#a8861d] font-display text-2xl font-bold text-[#1a1405] shadow-glow ring-1 ring-inset ring-white/40">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="eyebrow-gold">Selamat belajar</p>
                        <p class="mt-0.5 truncate font-display text-xl font-bold tracking-tight text-white sm:text-2xl">{{ $user->name }}</p>
                        <p class="mt-1 text-sm text-stone-300">NIS <span class="rounded-md border border-[#c9a227]/30 bg-[#c9a227]/10 px-1.5 py-0.5 font-mono font-semibold text-[#e9d189]">{{ $user->nis }}</span>
                            @if($user->class)
                                <span class="mx-1.5 text-stone-500">•</span> Kelas <span class="font-semibold text-white">{{ $user->class }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:flex">
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] px-3 py-2.5 text-center shadow-sm backdrop-blur sm:px-5">
                            <p class="font-display text-xl font-bold tabular-nums text-white">{{ $totalEbooks }}</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#d9c27a]">E-Book</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] px-3 py-2.5 text-center shadow-sm backdrop-blur sm:px-5">
                            <p class="font-display text-xl font-bold tabular-nums text-white">{{ $totalCategories }}</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#d9c27a]">Kategori</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search live (form GET agar tanpa-JS tetap mengarah ke katalog) --}}
            <form id="dash-form" method="GET" action="{{ route('ebooks.index') }}" class="card">
                <div class="flex items-center gap-3 border-b border-stone-200/70 px-4 py-3 transition focus-within:border-brand-900/50 focus-within:bg-stone-50/60 sm:px-5">
                    <svg class="h-5 w-5 shrink-0 text-stone-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14Zm0 2a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z"/><path d="M15.8 15.1l4.6 4.6-1.5 1.5-4.6-4.6 1.5-1.5Z"/></svg>
                    <input id="dash-q" type="search" name="q" autocomplete="off" aria-label="Cari e-book"
                        placeholder="Cari e-book&hellip;"
                        class="w-full min-w-0 border-0 bg-transparent p-0 text-base text-stone-900 placeholder:text-stone-400 focus:ring-0 sm:text-[15px]">
                    <span id="dash-spinner" class="hidden h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-stone-300 border-t-brand-900" aria-hidden="true"></span>
                </div>
                <div class="px-4 py-3 sm:px-5">
                    <div class="-mx-4 flex flex-nowrap items-center gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0 sm:pb-0">
                        <span class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Kategori</span>
                        <button type="button" data-dash-cat="" class="chip chip-active shrink-0">Semua</button>
                        @foreach($categories as $category)
                            <button type="button" data-dash-cat="{{ $category->id }}" class="chip shrink-0 sm:shrink">
                                {{ $category->name }} <span class="ml-1 text-stone-400">({{ $category->ebooks_count }})</span>
                            </button>
                        @endforeach
                    </div>
                    <p id="dash-status" class="mt-2 text-xs text-stone-500" aria-live="polite">Menampilkan e-book terbaru.</p>
                </div>
            </form>

            {{-- E-book terbaru / hasil live --}}
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h3 id="dash-grid-title" class="title-display text-xl">E-Book Terbaru</h3>
                    <a href="{{ route('ebooks.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-950">Lihat katalog</a>
                </div>

                <div id="dash-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($ebooks as $ebook)
                        <div class="card card-pad flex flex-col transition duration-200 hover:-translate-y-0.5 hover:shadow-lift">
                            <span class="badge-cat self-start">{{ $ebook->category->name ?? '-' }}</span>
                            <h4 class="title-display mt-2.5 line-clamp-2 text-lg leading-snug">{{ $ebook->title }}</h4>
                            <p class="mt-0.5 text-sm text-stone-500">{{ $ebook->author }}</p>
                            @if($ebook->description)
                                <p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-stone-500">{{ $ebook->description }}</p>
                            @endif
                            <a href="{{ route('ebooks.show', $ebook) }}" class="btn-primary mt-4 !py-2 !text-xs">Lihat Detail →</a>
                        </div>
                    @endforeach
                </div>
                <div id="dash-empty" class="hidden card card-pad text-center">
                    <p class="title-display text-base">Tidak ada hasil</p>
                    <p id="dash-empty-hint" class="mt-1 text-sm text-stone-500">Coba kata kunci lain.</p>
                </div>
            </div>

            {{-- Rak pribadi: simpanan + terakhir dibaca --}}
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="card card-pad">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="title-display text-lg">Simpanan Saya</h3>
                        <a href="{{ route('bookmarks.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-950">Lihat semua</a>
                    </div>
                    @if(($savedEbooks ?? collect())->isEmpty())
                        <p class="mt-2 text-sm text-stone-500">Belum ada simpanan. Buka detail e-book lalu tekan “☆ Simpan”.</p>
                    @else
                        <ul class="mt-3 divide-y divide-stone-100">
                            @foreach($savedEbooks as $saved)
                                <li class="py-2.5">
                                    <a href="{{ route('ebooks.show', $saved) }}" class="block transition hover:opacity-80">
                                        <p class="truncate font-semibold text-stone-900">{{ $saved->title }}</p>
                                        <p class="mt-0.5 text-xs text-stone-400">{{ $saved->author }} &middot; {{ $saved->category->name ?? '-' }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="card card-pad">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="title-display text-lg">Terakhir Dibaca</h3>
                        @if(($recentReads ?? collect())->isNotEmpty())
                            <form method="POST" action="{{ route('history.clear') }}" onsubmit="return confirm('Hapus seluruh riwayat baca?')">
                                @csrf @method('DELETE')
                                <button class="text-xs font-semibold text-stone-400 underline-offset-4 hover:text-stone-700 hover:underline">Hapus riwayat</button>
                            </form>
                        @endif
                    </div>
                    @if(($recentReads ?? collect())->isEmpty())
                        <p class="mt-2 text-sm text-stone-500">Belum ada riwayat. Mulai baca dari katalog.</p>
                    @else
                        <ul class="mt-3 divide-y divide-stone-100">
                            @foreach($recentReads as $read)
                                @if($read->ebook)
                                <li class="flex items-center gap-3 py-2.5">
                                    <a href="{{ route('ebooks.show', $read->ebook) }}" class="min-w-0 flex-1 transition hover:opacity-80">
                                        <p class="truncate font-semibold text-stone-900">{{ $read->ebook->title }}</p>
                                        <p class="mt-0.5 text-xs text-stone-400">{{ $read->last_read_at?->diffForHumans() }} &middot; dibuka {{ $read->read_count }}&times;</p>
                                    </a>
                                    <form method="POST" action="{{ route('history.destroy', $read->ebook_id) }}">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-stone-400 hover:text-red-700" title="Hapus entri ini">Hapus</button>
                                    </form>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>

<script>
(function () {
    const q = document.getElementById('dash-q');
    const grid = document.getElementById('dash-grid');
    const emptyBox = document.getElementById('dash-empty');
    const emptyHint = document.getElementById('dash-empty-hint');
    const status = document.getElementById('dash-status');
    const spinner = document.getElementById('dash-spinner');
    const title = document.getElementById('dash-grid-title');
    const chips = Array.from(document.querySelectorAll('[data-dash-cat]'));
    const dashForm = document.getElementById('dash-form');
    if (!q || !grid) return;

    const baseUrl = "{{ route('ebooks.index') }}";
    const initialHtml = grid.innerHTML;
    let timer = null, controller = null, activeCat = '';

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    async function run() {
        const keyword = q.value.trim();
        if (keyword === '' && activeCat === '') {
            if (controller) controller.abort();
            grid.innerHTML = initialHtml;
            grid.classList.remove('hidden');
            emptyBox.classList.add('hidden');
            title.textContent = 'E-Book Terbaru';
            status.textContent = 'Menampilkan e-book terbaru.';
            spinner.classList.add('hidden');
            return;
        }
        if (controller) controller.abort();
        controller = new AbortController();
        spinner.classList.remove('hidden');
        try {
            const url = new URL(baseUrl, window.location.origin);
            url.searchParams.set('ajax', '1');
            if (keyword) url.searchParams.set('q', keyword);
            if (activeCat) url.searchParams.set('category_id', activeCat);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }, signal: controller.signal });
            const json = await res.json();
            const items = json.data || [];
            if (items.length === 0) {
                grid.classList.add('hidden');
                emptyBox.classList.remove('hidden');
                if (emptyHint) emptyHint.textContent = keyword !== '' ? `Tidak ada hasil untuk \u201C${keyword}\u201D.` : 'Belum ada e-book pada kategori ini.';
                title.textContent = 'Hasil Pencarian';
                status.textContent = 'Tidak ada hasil. Coba kata kunci lain.';
            } else {
                emptyBox.classList.add('hidden');
                grid.classList.remove('hidden');
                grid.innerHTML = items.map((b) => `<div class="card card-pad flex flex-col transition duration-200 hover:-translate-y-0.5 hover:shadow-lift">
                    <span class="badge-cat self-start">${esc(b.category)}</span>
                    <h4 class="title-display mt-2.5 line-clamp-2 text-lg leading-snug">${esc(b.title)}</h4>
                    <p class="mt-0.5 text-sm text-stone-500">${esc(b.author)}</p>
                    ${b.description ? `<p class="mt-2 line-clamp-2 flex-1 text-sm leading-relaxed text-stone-500">${esc(b.description)}</p>` : ''}
                    <a href="${esc(b.detail_url)}" class="btn-primary mt-4 !py-2 !text-xs">Lihat Detail →</a></div>`).join('');
                title.textContent = 'Hasil Pencarian';
                status.textContent = `${items.length} hasil${keyword ? ` untuk \u201C${keyword}\u201D` : ''}.`;
            }
        } catch (e) {
            if (!e || e.name !== 'AbortError') status.textContent = 'Jaringan bermasalah — coba lagi.';
        } finally {
            spinner.classList.add('hidden');
        }
    }

    q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(run, 260); });
    // Enter: ke katalog dengan q + kategori aktif. Tanpa JS, form GET tetap terkirim (hanya q).
    if (dashForm) dashForm.addEventListener('submit', (e) => {
        if (!window.fetch) return; // biarkan submit normal
        e.preventDefault();
        const keyword = q.value.trim();
        const url = new URL(baseUrl, window.location.origin);
        if (keyword) url.searchParams.set('q', keyword);
        if (activeCat) url.searchParams.set('category_id', activeCat);
        window.location.href = url.toString();
    });
    chips.forEach((chip) => chip.addEventListener('click', () => {
        chips.forEach((c) => c.classList.remove('chip-active'));
        chip.classList.add('chip-active');
        activeCat = chip.getAttribute('data-dash-cat') || '';
        run();
    }));
})();
</script>
</x-app-layout>
