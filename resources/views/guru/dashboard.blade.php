<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Ruang Guru</p>
                <h2 class="title-display mt-2 text-2xl sm:text-3xl">Dashboard Guru</h2>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ route('ebooks.index') }}" class="btn-ghost !px-4 !py-2 !text-xs">Katalog</a>
                <a href="{{ route('videos.index') }}" class="btn-gold !px-4 !py-2 !text-xs">Video →</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- Profil guru --}}
            <div class="card-dark">
                <div class="relative flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-b from-[#e3c964] to-[#a8861d] font-display text-2xl font-bold text-[#1a1405] shadow-glow ring-1 ring-inset ring-white/40">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="eyebrow-gold">Ruang Guru</p>
                        <p class="mt-0.5 truncate font-display text-xl font-bold tracking-tight text-white sm:text-2xl">{{ $user->name }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 sm:flex">
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] px-4 py-2.5 text-center shadow-sm backdrop-blur sm:px-5">
                            <p class="font-display text-xl font-bold tabular-nums text-white">{{ $totalEbooks }}</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#d9c27a]">E-Book</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] px-4 py-2.5 text-center shadow-sm backdrop-blur sm:px-5">
                            <p class="font-display text-xl font-bold tabular-nums text-white">{{ $totalCategories }}</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#d9c27a]">Kategori</p>
                        </div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.06] px-4 py-2.5 text-center shadow-sm backdrop-blur sm:px-5">
                            <p class="font-display text-xl font-bold tabular-nums text-white">{{ $totalVideos }}</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#d9c27a]">Video</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Akun saya --}}
            <div class="card card-pad flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h3 class="title-display text-lg">Akun Saya</h3>
                    <p class="mt-0.5 text-sm text-stone-500">Ubah nama, username, dan password Anda.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn-primary shrink-0 !py-2 !text-xs">Buka Profil</a>
            </div>

            {{-- Kontribusi saya --}}
            <div class="card card-pad">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="title-display text-lg">Kontribusi Saya</h3>
                        <p class="mt-0.5 text-sm text-stone-500">{{ $myEbooksCount ?? 0 }}/10 e-book • {{ $myVideosCount ?? 0 }}/10 video ditambahkan oleh Anda.</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('guru.ebooks.create') }}" class="btn-primary shrink-0 !py-2 !text-xs">+ Tambah E-Book</a>
                        <a href="{{ route('guru.videos.create') }}" class="btn-gold shrink-0 !py-2 !text-xs">+ Tambah Video</a>
                    </div>
                </div>
                @if(($myEbooks ?? collect())->isNotEmpty() || ($myVideos ?? collect())->isNotEmpty())
                    <ul class="mt-3 divide-y divide-stone-100">
                        @foreach($myEbooks ?? [] as $mine)
                            <li class="flex items-center gap-3 py-2">
                                <div class="book-frame h-12 w-9">
                                    @if($mine->cover)
                                        <img src="{{ $mine->cover_url }}" alt="Sampul {{ $mine->title }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    @else
                                        <span class="font-display text-base font-semibold text-stone-300" aria-hidden="true">{{ strtoupper(mb_substr($mine->title, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-stone-800">{{ $mine->title }}</p>
                                    <p class="mt-0.5 text-xs text-stone-400">{{ $mine->category->name ?? '-' }} &middot; e-book</p>
                                </div>
                                <a href="{{ route('ebooks.show', $mine) }}" class="shrink-0 text-xs font-semibold text-brand-800 hover:underline">Lihat</a>
                            </li>
                        @endforeach
                        @foreach($myVideos ?? [] as $mine)
                            <li class="flex items-center gap-3 py-2">
                                <span class="flex h-12 w-9 shrink-0 items-center justify-center rounded-lg border border-stone-200/90 bg-gradient-to-b from-[#f7f3e8] to-[#ece5d0] text-sm shadow-sm" aria-hidden="true">🎬</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-stone-800">{{ $mine->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-stone-400">{{ $mine->ebook->title ?? '' }} &middot; video</p>
                                </div>
                                @if($mine->ebook)
                                    <a href="{{ route('ebooks.show', $mine->ebook) }}" class="shrink-0 text-xs font-semibold text-brand-800 hover:underline">Lihat</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-3 rounded-lg bg-stone-50 p-3 text-sm text-stone-500">Belum ada kontribusi. Yuk tambah e-book atau link video YouTube pertama Anda.</p>
                @endif
            </div>

            {{-- E-book terbaru --}}
            <div>
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="title-display text-xl">E-Book Terbaru</h3>
                    <a href="{{ route('ebooks.index') }}" class="text-sm font-semibold text-brand-800 hover:text-brand-950">Lihat katalog</a>
                </div>

                @if($ebooks->isEmpty())
                    <div class="card card-pad text-center text-sm text-stone-500">Belum ada e-book. Silakan kembali lagi nanti.</div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($ebooks as $ebook)
                            <article class="card card-hover group flex flex-col">
                                <div class="flex gap-3 p-4 sm:gap-4 sm:p-5">
                                    <div class="book-frame h-28 w-20 shrink-0 sm:h-32 sm:w-24">
                                        @if($ebook->cover)
                                            <img src="{{ $ebook->cover_url }}" alt="Sampul {{ $ebook->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.04]" loading="lazy" decoding="async">
                                        @else
                                            <span class="font-display text-4xl font-semibold text-stone-300" aria-hidden="true">{{ strtoupper(mb_substr($ebook->title, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="badge-cat">{{ $ebook->category->name ?? '-' }}</span>
                                        <h4 class="title-display mt-2 line-clamp-2 text-base leading-snug sm:text-[1.05rem]">{{ $ebook->title }}</h4>
                                        <p class="mt-0.5 truncate text-sm text-stone-500">{{ $ebook->author }}</p>
                                        <p class="mt-2 text-xs font-medium tracking-wide text-stone-400">{{ $ebook->videos_count ?? 0 }} video pembelajaran</p>
                                    </div>
                                </div>
                                @if($ebook->description)
                                    <p class="px-4 pb-1 line-clamp-2 flex-1 text-sm leading-relaxed text-stone-500 sm:px-5">{{ $ebook->description }}</p>
                                @endif
                                <div class="mt-auto p-4 sm:p-5 sm:pt-3">
                                    <a href="{{ route('ebooks.show', $ebook) }}" class="btn-primary w-full !py-2 !text-[13px]">Lihat Detail →</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
