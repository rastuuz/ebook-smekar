<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan Digital Sekolah</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=source-serif-4:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f7f5ef] font-sans text-stone-900 antialiased">
<div class="flex min-h-screen flex-col">
    <header class="relative bg-[#0e1b33] text-white">
        <div class="relative mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center gap-2.5">
                <x-school-logo size="sm" />
                <span class="leading-tight">
                    <span class="block font-display text-base font-semibold tracking-wide text-white">E-Library</span>
                    <span class="block text-[11px] font-medium uppercase tracking-[0.2em] text-[#d9c27a]">SMKN 1 Dukuhturi</span>
                </span>
            </a>
            <nav class="flex items-center gap-2 text-sm">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-gold !px-4 !py-2">Dashboard</a>
                @else
                    <span class="hidden items-center gap-3 sm:flex">
                        <a href="{{ route('login.guru') }}" class="rounded-lg px-2 py-2 font-medium text-stone-300 transition hover:text-white">Guru</a>
                        <a href="{{ route('login.admin') }}" class="rounded-lg px-2 py-2 font-medium text-stone-300 transition hover:text-white">Admin</a>
                    </span>
                    <a href="{{ route('login.nis') }}" class="btn-gold !px-4 !py-2">Masuk dengan NIS</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        {{-- Hero --}}
        <div class="card-dark">
            <div class="pointer-events-none absolute -left-16 -top-24 h-64 w-64 rounded-full bg-[#1d4fd7]/25 blur-3xl" aria-hidden="true"></div>
            <span aria-hidden="true" class="pointer-events-none absolute -bottom-12 -right-2 hidden select-none font-display text-[13rem] font-bold leading-none text-white/[0.05] lg:block">E</span>
            <div class="relative p-6 sm:p-12 lg:p-14">
                <div>
                    <span class="eyebrow-gold">Perpustakaan Digital SMKN 1 Dukuhturi</span>
                </div>
                <h1 class="mt-4 max-w-2xl font-display text-3xl font-semibold leading-[1.08] tracking-tight text-white sm:text-5xl">Temukan E-Book <span class="gold-gradient-text">untuk Mendukung Belajarmu.</span></h1>
                <p class="mt-4 max-w-xl text-sm leading-relaxed text-stone-300 sm:text-base">Akses berbagai koleksi buku digital dan materi pembelajaran dalam satu platform. Cari buku berdasarkan kategori, baca materi pelajaran, dan pelajari hal baru kapan saja untuk mendukung kegiatan belajar di sekolah.</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/[0.06] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-200">PDF + Video</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/[0.06] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-200">Asisten Belajar</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#c9a227]/30 bg-[#c9a227]/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#e9d189]">Akses 24/7</span>
                </div>
                <div class="mt-7 flex flex-col gap-2 sm:flex-row sm:items-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-gold">Buka Dashboard →</a>
                    @else
                        <a href="{{ route('login.nis') }}" class="btn-gold">Masuk dengan NIS →</a>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <a href="{{ route('login.guru') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/[0.04] px-5 py-2.5 text-center text-sm font-medium text-stone-200 backdrop-blur transition duration-200 hover:border-[#c9a227]/40 hover:bg-white/[0.08] hover:text-white">Masuk sebagai Guru</a>
                            <a href="{{ route('login.admin') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/[0.04] px-5 py-2.5 text-center text-sm font-medium text-stone-200 backdrop-blur transition duration-200 hover:border-[#c9a227]/40 hover:bg-white/[0.08] hover:text-white">Masuk sebagai Admin</a>
                        </div>
                    @endauth
                </div>
                <div class="mt-8 flex flex-wrap gap-x-8 gap-y-5 border-t border-white/10 pt-6">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-lg border border-[#c9a227]/30 bg-[#c9a227]/10"><svg class="h-4 w-4 text-[#d9c27a]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 2h7v20H6a2 2 0 0 1-2-2V4a2 2 0 0 1 0-2Zm9 0h5a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-5V2Z"/></svg></span>
                        <span><span class="stat-num block">{{ $totalEbooks ?? $ebooks->count() }}+</span>
                        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">E-Book</span></span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-lg border border-[#c9a227]/30 bg-[#c9a227]/10"><svg class="h-4 w-4 text-[#d9c27a]" viewBox="0 0 24 24" fill="currentColor" fill-rule="evenodd" aria-hidden="true"><path d="M3 2h8l11 11-8 8L3 10V2Zm6.5 4.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z"/></svg></span>
                        <span><span class="stat-num block">{{ $totalCategories ?? $categories->count() }}</span>
                        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Kategori</span></span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-lg border border-[#c9a227]/30 bg-[#c9a227]/10"><svg class="h-4 w-4 text-[#d9c27a]" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1a11 11 0 1 0 0 22 11 11 0 0 0 0-22Z"/><path fill="#0e1b33" d="M11 6h2v6.6l4.2 2.5-.9 1.5L11 13.4V6Z"/></svg></span>
                        <span><span class="stat-num block">24/7</span>
                        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Akses</span></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Terbaru --}}
        <div class="mt-14">
            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Koleksi Terbaru</p>
                    <h2 class="title-display mt-2 text-2xl sm:text-3xl">E-Book Terbaru</h2>
                    <p class="mt-1 max-w-lg text-sm text-stone-500">Baru ditambahkan — masuk untuk membaca.</p>
                </div>
                <a href="{{ route('login.nis') }}" class="btn-ghost hidden shrink-0 !py-2 !text-xs sm:inline-flex">Masuk untuk Membaca →</a>
            </div>
            @if($ebooks->isEmpty())
                <div class="card card-pad text-center text-sm text-stone-500">Belum ada e-book. Silakan kembali lagi nanti.</div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($ebooks as $ebook)
                        <article class="card card-hover group flex flex-col">
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
                                </div>
                            </div>
                            @if($ebook->description)
                                <p class="px-4 pb-1 line-clamp-2 text-sm leading-relaxed text-stone-500 sm:px-5">{{ $ebook->description }}</p>
                            @endif
                            <div class="mt-auto p-4 sm:p-5 sm:pt-3">
                                <a href="{{ route('login.nis') }}" class="btn-primary w-full !py-2 !text-[13px]">Masuk untuk Membaca →</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Keunggulan --}}
        <div class="mt-14">
            <div class="mb-5">
                <p class="eyebrow">Keunggulan</p>
                <h2 class="title-display mt-2 text-2xl sm:text-3xl">Keunggulan Perpustakaan</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="card card-hover card-pad">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                        <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2V5Z"/><path d="M4 19a2 2 0 0 1 2-2h13"/></svg>
                    </span>
                    <h3 class="title-display mt-4 text-base">Koleksi Buku Digital</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Temukan berbagai e-book dan materi pembelajaran dari beragam mata pelajaran yang dapat membantu meningkatkan pemahaman siswa.</p>
                </div>
                <div class="card card-hover card-pad">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                        <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                    </span>
                    <h3 class="title-display mt-4 text-base">Pencarian Buku yang Mudah</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Cari buku berdasarkan judul, kategori, atau materi yang dibutuhkan tanpa harus menelusuri seluruh koleksi secara manual.</p>
                </div>
                <div class="card card-hover card-pad">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                        <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="12" height="12" rx="2"/><path d="m15 10.5 6-3.5v10l-6-3.5"/></svg>
                    </span>
                    <h3 class="title-display mt-4 text-base">Video Pembelajaran</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Tonton video pendukung tiap materi langsung di halaman e-book.</p>
                </div>
                <div class="card card-hover card-pad">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                        <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3 20c.8-3 3.2-5 6-5s5.2 2 6 5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18.3 15.3c1.6.8 2.7 2.3 3.2 4.2"/></svg>
                    </span>
                    <h3 class="title-display mt-4 text-base">Akses Khusus Warga Sekolah</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Masuk menggunakan NIS untuk siswa, serta akun khusus untuk guru dan admin agar pengelolaan perpustakaan lebih teratur.</p>
                </div>
            </div>
        </div>

        {{-- Kategori --}}
        <div class="card card-pad mt-8">
            <p class="eyebrow">Rak Berdasarkan Minat</p>
            <h2 class="title-display mt-2 text-xl">Jelajahi Kategori</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($categories as $category)
                    <a href="{{ auth()->check() ? route('ebooks.index', ['category_id' => $category->id]) : route('login.nis') }}" class="chip !px-4 !py-2">{{ $category->name }} <span class="ml-1.5 rounded-full bg-stone-100 px-1.5 py-0.5 text-[11px] font-bold tabular-nums text-stone-500">{{ $category->ebooks_count }}</span></a>
                @endforeach
            </div>
        </div>

        {{-- Cara memakai --}}
        <div class="mt-14">
            <div class="mb-5">
                <p class="eyebrow">Langkah Mudah</p>
                <h2 class="title-display mt-2 text-2xl sm:text-3xl">Cara Memakai</h2>
                <p class="mt-1 max-w-lg text-sm text-stone-500">Tiga langkah untuk mulai membaca.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="card card-hover card-pad">
                    <div class="flex items-center justify-between">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                            <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="11" r="2"/><path d="M5.5 16c.6-1.2 1.7-2 3-2s2.4.8 3 2"/><path d="M14 9.5h4M14 13h4"/></svg>
                        </span>
                        <span class="font-display text-sm font-bold tracking-widest text-[#a8861d]">01</span>
                    </div>
                    <h3 class="title-display mt-4 text-base">Masuk dengan NIS</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Siswa cukup memasukkan Nomor Induk Siswa — tanpa password. Guru dan admin memakai username masing-masing.</p>
                    @auth
                        <a href="{{ route('dashboard') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:underline">Buka dashboard</a>
                    @else
                        <a href="{{ route('login.nis') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:underline">Masuk sekarang</a>
                    @endauth
                </div>
                <div class="card card-hover card-pad">
                    <div class="flex items-center justify-between">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                            <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg>
                        </span>
                        <span class="font-display text-sm font-bold tracking-widest text-[#a8861d]">02</span>
                    </div>
                    <h3 class="title-display mt-4 text-base">Cari &amp; Baca</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Jelajahi katalog berdasarkan kategori, pratinjau PDF, dan tonton video pendukung tiap materi.</p>
                    @auth
                        <a href="{{ route('ebooks.index') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:underline">Buka katalog</a>
                    @else
                        <a href="{{ route('login.nis') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:underline">Lihat katalog</a>
                    @endauth
                </div>
                <div class="card card-hover card-pad">
                    <div class="flex items-center justify-between">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#c9a227]/30 bg-[#faf3dd]">
                            <svg class="h-5 w-5 text-[#a8861d]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.5 0-3-.4-4.2-1.1L3 20l1.1-4.3A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/></svg>
                        </span>
                        <span class="font-display text-sm font-bold tracking-widest text-[#a8861d]">03</span>
                    </div>
                    <h3 class="title-display mt-4 text-base">Tanya Asisten</h3>
                    <p class="mt-1 text-sm leading-relaxed text-stone-500">Belum paham? Tanyakan materinya ke asisten belajar.</p>
                    @auth
                        <a href="{{ route('ebooks.index') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:underline">Pilih e-book</a>
                    @else
                        <a href="{{ route('login.nis') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:underline">Coba sekarang</a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Ajakan bawah --}}
        <div class="card-dark mt-14">
            <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-[#1d4fd7]/25 blur-3xl" aria-hidden="true"></div>
            <div class="relative p-6 sm:p-12">
                <h2 class="max-w-xl font-display text-2xl font-semibold leading-tight tracking-tight text-white sm:text-3xl">Belajar Lebih Mudah, Kapan Saja.</h2>
                <p class="mt-3 max-w-xl text-sm leading-relaxed text-stone-300 sm:text-base">Jadikan perpustakaan digital sebagai teman belajar sehari-hari. Temukan referensi baru, perluas wawasan, dan tingkatkan semangat belajar melalui koleksi digital SMKN 1 Dukuhturi.</p>
                <div class="mt-6">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-gold">Buka Dashboard →</a>
                    @else
                        <a href="{{ route('login.nis') }}" class="btn-gold">Masuk dengan NIS →</a>
                    @endauth
                </div>
            </div>
        </div>
    </main>

    <footer class="px-4 py-12 text-center">
        <p class="font-display text-sm font-semibold tracking-wide text-stone-700">E-Library &middot; SMKN 1 Dukuhturi</p>
    </footer>
</div>
</body>
</html>
