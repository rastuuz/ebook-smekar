<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Koleksi &middot; Katalog</p>
                <h2 class="title-display mt-2 text-2xl sm:text-[1.7rem]">Katalog E-Book</h2>
                <p class="mt-1 max-w-xl text-sm text-stone-500">Ketik untuk mencari seketika.</p>
            </div>
            @if(auth()->user()?->isStaff())
                <a href="{{ route('guru.ebooks.create') }}" class="btn-primary shrink-0 !py-2 !text-xs">+ Tambah E-Book</a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- Search + filter : live, tanpa tombol Cari (tetap berupa form GET agar tanpa-JS tetap jalan) --}}
            <form id="live-form" method="GET" action="{{ route('ebooks.index') }}" class="card shadow-soft">
                <div class="flex items-center gap-3 border-b border-stone-200/70 px-4 py-3 transition focus-within:border-[#c9a227]/60 focus-within:bg-[#faf3dd]/40 sm:px-5">
                    <svg class="h-5 w-5 shrink-0 text-stone-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14Zm0 2a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z"/><path d="M15.8 15.1l4.6 4.6-1.5 1.5-4.6-4.6 1.5-1.5Z"/></svg>
                    <input id="live-q" type="search" name="q" value="{{ $search }}" autocomplete="off" aria-label="Cari e-book"
                        placeholder="Ketik judul, penulis, atau kategori&hellip;"
                        class="w-full min-w-0 border-0 bg-transparent p-0 text-base text-stone-900 placeholder:text-stone-400 focus:ring-0 sm:text-[15px]">
                    <span id="live-spinner" class="hidden h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-stone-300 border-t-brand-900" aria-hidden="true"></span>
                    <button id="live-clear" type="button" class="hidden shrink-0 text-xs font-semibold text-stone-400 underline-offset-4 transition hover:text-stone-700 hover:underline">Bersihkan</button>
                </div>
                <div class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:px-5">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Kategori</span>
                    <select id="live-category" name="category_id" class="input w-full !mt-0 sm:max-w-xs">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string)$categoryId === (string)$category->id)>
                                {{ $category->name }} ({{ $category->ebooks_count }})
                            </option>
                        @endforeach
                    </select>
                    <p id="live-status" class="sm:ml-auto text-xs text-stone-500" aria-live="polite">
                        @if($search !== '' || !empty($categoryId))
                            {{ $ebooks->total() }} hasil ditemukan.
                        @else
                            {{ $ebooks->total() }} e-book tersedia.
                        @endif
                    </p>
                    <a id="live-reset" href="{{ route('ebooks.index') }}" class="{{ ($search !== '' || !empty($categoryId)) ? '' : 'hidden' }} self-start text-xs font-semibold text-brand-800 underline-offset-4 hover:underline sm:self-auto">Atur ulang</a>
                </div>
            </form>

            {{-- Grid hasil --}}
            <div id="ebook-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($ebooks as $ebook)
                    <article class="card card-hover group flex flex-col" data-ebook-card>
                        <div class="flex gap-3 p-4 sm:gap-4 sm:p-5">
                            <div class="book-frame h-28 w-20 sm:h-32 sm:w-24">
                                @if($ebook->cover)
                                    <img src="{{ $ebook->cover_url }}" alt="Sampul {{ $ebook->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]" loading="lazy" decoding="async">
                                @else
                                    <span class="font-display text-4xl font-semibold text-stone-300" aria-hidden="true">{{ strtoupper(mb_substr($ebook->title, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="badge-cat">{{ $ebook->category->name ?? '-' }}</span>
                                <h3 class="title-display mt-2 line-clamp-2 text-base leading-snug sm:text-[1.05rem]">{{ $ebook->title }}</h3>
                                <p class="mt-0.5 truncate text-sm text-stone-500">{{ $ebook->author }}</p>
                                <p class="mt-2 text-xs font-medium tracking-wide text-stone-400">{{ $ebook->videos_count }} video pembelajaran</p>
                            </div>
                        </div>
                        @if($ebook->description)
                            <p class="px-4 pb-1 line-clamp-2 text-sm leading-relaxed text-stone-500 sm:px-5">{{ $ebook->description }}</p>
                        @endif
                        <div class="mt-auto p-4 sm:p-5 sm:pt-3">
                            <a href="{{ route('ebooks.show', $ebook) }}" class="btn-primary w-full !py-2 !text-[13px]">Lihat Detail →</a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Skeleton saat mengetik --}}
            <div id="ebook-skeleton" class="hidden grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
                @for($i = 0; $i < 6; $i++)
                    <div class="card p-5">
                        <div class="flex gap-4">
                            <div class="h-32 w-24 animate-pulse rounded-lg bg-stone-200/70"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 w-20 animate-pulse rounded-full bg-stone-200/70"></div>
                                <div class="h-4 w-full animate-pulse rounded bg-stone-200/70"></div>
                                <div class="h-4 w-2/3 animate-pulse rounded bg-stone-200/70"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            {{-- Kosong --}}
            <div id="ebook-empty" class="{{ $ebooks->isEmpty() ? '' : 'hidden' }} card card-pad py-12 text-center">
                <p class="title-display text-lg">Tidak ada e-book ditemukan</p>
                <p id="ebook-empty-hint" class="mx-auto mt-1 max-w-sm text-sm text-stone-500">Coba kata kunci lain atau ubah filter kategori.</p>
                <button id="ebook-empty-reset" type="button" class="btn-ghost mt-4">Tampilkan semua</button>
            </div>

            <div id="pagination-wrap" class="mt-4 overflow-x-auto">{{ $ebooks->links() }}</div>
        </div>
    </div>

<script>
(function () {
    const q = document.getElementById('live-q');
    const cat = document.getElementById('live-category');
    const grid = document.getElementById('ebook-grid');
    const skeleton = document.getElementById('ebook-skeleton');
    const emptyBox = document.getElementById('ebook-empty');
    const emptyHint = document.getElementById('ebook-empty-hint');
    const status = document.getElementById('live-status');
    const spinner = document.getElementById('live-spinner');
    const clearBtn = document.getElementById('live-clear');
    const resetLink = document.getElementById('live-reset');
    const pagination = document.getElementById('pagination-wrap');
    const emptyReset = document.getElementById('ebook-empty-reset');
    const liveForm = document.getElementById('live-form');
    if (!q || !grid) return;

    const baseUrl = "{{ route('ebooks.index') }}";
    let timer = null;
    let controller = null;
    let firstLoad = true;

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function cardHtml(b) {
        const initial = esc(String(b.title ?? '').charAt(0).toUpperCase());
        const cover = b.cover_url
            ? `<img src="${esc(b.cover_url)}" alt="Sampul ${esc(b.title)}" class="h-full w-full object-cover" loading="lazy" decoding="async">`
            : `<span class="font-display text-4xl font-semibold text-stone-300">${initial}</span>`;
        const desc = b.description ? `<p class="px-4 pb-1 line-clamp-2 text-sm leading-relaxed text-stone-500 sm:px-5">${esc(b.description)}</p>` : '';
        return `<article class="card card-hover group flex flex-col">
            <div class="flex gap-3 p-4 sm:gap-4 sm:p-5">
                <div class="book-frame h-28 w-20 sm:h-32 sm:w-24">${cover}</div>
                <div class="min-w-0 flex-1">
                    <span class="badge-cat">${esc(b.category)}</span>
                    <h3 class="title-display mt-2 line-clamp-2 text-base leading-snug sm:text-[1.05rem]">${esc(b.title)}</h3>
                    <p class="mt-0.5 truncate text-sm text-stone-500">${esc(b.author)}</p>
                    <p class="mt-2 text-xs font-medium tracking-wide text-stone-400">${esc(b.videos_count)} video pembelajaran</p>
                </div>
            </div>${desc}
            <div class="mt-auto p-4 sm:p-5 sm:pt-3">
                <a href="${esc(b.detail_url)}" class="btn-primary w-full !py-2 !text-[13px]">Lihat Detail →</a>
            </div></article>`;
    }

    function syncChrome(keyword, categoryVal, total) {
        const hasFilter = keyword !== '' || categoryVal !== '';
        clearBtn.classList.toggle('hidden', keyword === '');
        resetLink.classList.toggle('hidden', !hasFilter);
        if (pagination) pagination.style.display = hasFilter ? 'none' : '';
        if (hasFilter) {
            status.textContent = total > 0 ? `${total} hasil untuk \u201C${keyword || 'semua kata kunci'}\u201D.` : `Tidak ada hasil untuk \u201C${keyword}\u201D.`;
        } else {
            status.textContent = 'Menampilkan e-book terbaru.';
        }
        const url = new URL(baseUrl, window.location.origin);
        if (keyword) url.searchParams.set('q', keyword);
        if (categoryVal) url.searchParams.set('category_id', categoryVal);
        window.history.replaceState(null, '', url.toString());
    }

    async function runLive() {
        const keyword = q.value.trim();
        const categoryVal = cat.value;
        if (controller) controller.abort();
        controller = new AbortController();
        spinner.classList.remove('hidden');
        if (firstLoad && grid.children.length > 0) {
            // jangan tampilkan skeleton pada pemuatan awal yang sudah ada datanya
        } else {
            grid.classList.add('hidden');
            emptyBox.classList.add('hidden');
            skeleton.classList.remove('hidden');
        }
        try {
            const url = new URL(baseUrl, window.location.origin);
            url.searchParams.set('ajax', '1');
            if (keyword) url.searchParams.set('q', keyword);
            if (categoryVal) url.searchParams.set('category_id', categoryVal);
            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }, signal: controller.signal });
            if (!res.ok) throw new Error('bad');
            const json = await res.json();
            const items = json.data || [];
            const total = typeof json.total === 'number' ? json.total : items.length;
            grid.innerHTML = items.map(cardHtml).join('');
            firstLoad = false;
            skeleton.classList.add('hidden');
            const hasData = items.length > 0;
            grid.classList.toggle('hidden', !hasData);
            emptyBox.classList.toggle('hidden', hasData);
            if (!hasData && emptyHint) {
                emptyHint.textContent = keyword !== ''
                    ? `Tidak ada hasil untuk \u201C${keyword}\u201D. Coba kata kunci lain atau ubah kategori.`
                    : 'Belum ada e-book pada kategori ini.';
            }
            syncChrome(keyword, categoryVal, total);
        } catch (e) {
            if (e && e.name === 'AbortError') return;
            skeleton.classList.add('hidden');
            grid.classList.remove('hidden');
            status.textContent = 'Jaringan bermasalah — menampilkan hasil terakhir.';
        } finally {
            spinner.classList.add('hidden');
        }
    }

    function schedule() {
        clearTimeout(timer);
        timer = setTimeout(runLive, 260);
    }

    q.addEventListener('input', schedule);
    cat.addEventListener('change', runLive);
    // Enter / submit form jangan reload — tetap live-search. Tanpa JS, form GET ini tetap terkirim normal.
    if (liveForm) liveForm.addEventListener('submit', (e) => e.preventDefault());
    clearBtn.addEventListener('click', () => { q.value = ''; q.focus(); runLive(); });
    resetLink.addEventListener('click', (e) => {
        if (!window.fetch) return; // biarkan fallback server-side
        e.preventDefault();
        q.value = ''; cat.value = '';
        runLive();
    });
    if (emptyReset) emptyReset.addEventListener('click', () => { q.value = ''; cat.value = ''; runLive(); });

    // status awal
    syncChrome(q.value.trim(), cat.value, {{ $ebooks->total() }});
    if (pagination && (q.value.trim() !== '' || cat.value !== '')) pagination.style.display = 'none';
})();
</script>
</x-app-layout>
