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
                    <ul class="mt-3 space-y-1.5 text-sm">
                        @foreach($myEbooks ?? [] as $mine)
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-stone-50 px-3 py-2">
                                <span class="min-w-0 truncate font-semibold text-stone-800">📘 {{ $mine->title }}</span>
                                <a href="{{ route('ebooks.show', $mine) }}" class="shrink-0 text-xs font-semibold text-brand-800 hover:underline">Lihat</a>
                            </li>
                        @endforeach
                        @foreach($myVideos ?? [] as $mine)
                            <li class="flex items-center justify-between gap-2 rounded-lg bg-stone-50 px-3 py-2">
                                <span class="min-w-0 truncate font-semibold text-stone-800">🎬 {{ $mine->title }}</span>
                                <span class="shrink-0 text-xs text-stone-400">{{ $mine->ebook->title ?? '' }}</span>
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
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
