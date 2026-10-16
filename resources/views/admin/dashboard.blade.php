@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="card-dark card-pad">
        <p class="eyebrow-gold">Total E-Book</p>
        <p class="title-display mt-3 !text-white text-5xl tabular-nums">{{ $totalEbooks }}</p>
        <a href="{{ route('admin.ebooks.index') }}" class="mt-3 inline-block text-sm font-semibold text-[#d9c27a] underline-offset-4 hover:text-white hover:underline">Kelola koleksi →</a>
    </div>
    <div class="card card-pad card-hover">
        <p class="eyebrow">Total Kategori</p>
        <p class="title-display mt-3 text-5xl tabular-nums">{{ $totalCategories }}</p>
        <a href="{{ route('admin.categories.index') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">Kelola kategori →</a>
    </div>
    <div class="card card-pad card-hover">
        <p class="eyebrow">Total Siswa</p>
        <p class="title-display mt-3 text-5xl tabular-nums">{{ $totalSiswa }}</p>
        <a href="{{ route('admin.users.index') }}" class="mt-3 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">Kelola siswa →</a>
    </div>
</div>

<div class="mt-4 grid gap-4 sm:grid-cols-3">
    <div class="card card-pad card-hover">
        <p class="eyebrow">Total Guru</p>
        <p class="title-display mt-3 text-4xl tabular-nums">{{ $totalGuru ?? 0 }}</p>
        <a href="{{ route('admin.users.index', ['role' => \App\Models\User::ROLE_GURU]) }}" class="mt-2 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">Kelola guru →</a>
    </div>
    <div class="card card-pad card-hover">
        <p class="eyebrow">Total Video</p>
        <p class="title-display mt-3 text-4xl tabular-nums">{{ $totalVideos ?? 0 }}</p>
        <a href="{{ route('admin.videos.index') }}" class="mt-2 inline-block text-sm font-semibold text-brand-800 underline-offset-4 hover:text-brand-950 hover:underline">Kelola video →</a>
    </div>
    <div class="card card-pad card-hover">
        <p class="eyebrow">Perlu Perhatian</p>
        <p class="title-display mt-3 text-4xl tabular-nums">{{ ($missingPdf ?? 0) + ($notIndexed ?? 0) }}</p>
        <p class="mt-2 text-xs text-stone-500">{{ $missingPdf ?? 0 }} tanpa PDF &middot; {{ $notIndexed ?? 0 }} teks tak terbaca</p>
    </div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-2">
    <div class="card overflow-hidden">
        <div class="border-b border-stone-200/70 bg-[#0e1b33] p-4 sm:px-5">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-300">Baru Ditambahkan</p>
            <div class="mt-1 flex items-baseline justify-between gap-3">
                <h3 class="font-display text-base font-semibold text-white">E-Book Terbaru</h3>
                <a href="{{ route('admin.videos.index') }}" class="shrink-0 text-sm font-semibold text-[#d9c27a] underline-offset-4 hover:underline">Video</a>
            </div>
        </div>
        <ul class="divide-y divide-stone-100">
            @forelse($latestEbooks as $ebook)
                <li class="p-4 transition hover:bg-stone-50 sm:px-5">
                    <p class="truncate font-display text-[1.05rem] font-semibold text-stone-900">{{ $ebook->title }}</p>
                    <p class="mt-0.5 text-xs tracking-wide text-stone-400">{{ $ebook->author }} &middot; {{ $ebook->category->name ?? '-' }}</p>
                </li>
            @empty
                <li class="p-4 text-sm text-stone-400">Belum ada e-book.</li>
            @endforelse
        </ul>
    </div>
    <div class="card card-pad">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-400">Paling Diminati</p>
        <h3 class="title-display mt-1 text-base">E-Book Terpopuler</h3>
        <p class="mt-0.5 text-xs text-stone-400">Berdasar simpanan + riwayat baca.</p>
        <ul class="mt-3 divide-y divide-stone-100">
            @forelse($popular ?? [] as $ebook)
                <li class="py-2.5">
                    <p class="truncate font-display text-[1.05rem] font-semibold text-stone-900">{{ $ebook->title }}</p>
                    <p class="mt-0.5 text-xs tracking-wide text-stone-400">{{ $ebook->bookmarks_count }} simpanan &middot; {{ $ebook->reading_histories_count }} dibaca &middot; {{ $ebook->category->name ?? '-' }}</p>
                </li>
            @empty
                <li class="py-2.5 text-sm text-stone-400">Belum ada aktivitas baca.</li>
            @endforelse
        </ul>
    </div>
</div>

@if(($needsAttention ?? collect())->isNotEmpty())
<div class="card mt-4 overflow-hidden">
    <div class="border-b border-amber-200/70 bg-amber-50 p-4 sm:px-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-700">Perlu Perhatian</p>
        <h3 class="font-display mt-1 text-base font-semibold text-stone-900">E-Book tanpa PDF / teks tak terbaca</h3>
    </div>
    <ul class="divide-y divide-stone-100">
        @foreach($needsAttention as $ebook)
            <li class="flex flex-wrap items-center gap-2 p-4 sm:px-5">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-stone-900">{{ $ebook->title }}</p>
                    <p class="mt-0.5 text-xs text-stone-400">{{ $ebook->author }} &middot; {{ $ebook->file ? 'PDF ada, teks tak terbaca (scan?)' : 'PDF belum diunggah' }}</p>
                </div>
                <a href="{{ route('admin.ebooks.edit', $ebook) }}" class="btn-row">Perbaiki</a>
            </li>
        @endforeach
    </ul>
</div>
@endif

<div class="card card-pad mt-4">
    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-400">Aksi</p>
    <h3 class="title-display mt-1 text-base">Menu Cepat</h3>
        <div class="mt-3 divide-y divide-stone-100 border-y border-stone-100 text-sm">
            <a href="{{ route('admin.ebooks.create') }}" class="group flex items-baseline gap-4 py-3 transition hover:bg-stone-50">
                <span class="w-8 shrink-0 font-display text-sm font-semibold text-stone-300 transition group-hover:text-[#8a6d1b]">01</span>
                <span class="flex-1 font-medium text-stone-800">Tambah E-Book <span class="font-normal text-stone-400">— cover + PDF</span></span>
            </a>
            <a href="{{ route('admin.categories.create') }}" class="group flex items-baseline gap-4 py-3 transition hover:bg-stone-50">
                <span class="w-8 shrink-0 font-display text-sm font-semibold text-stone-300 transition group-hover:text-[#8a6d1b]">02</span>
                <span class="flex-1 font-medium text-stone-800">Tambah Kategori</span>
            </a>
            <a href="{{ route('admin.videos.create') }}" class="group flex items-baseline gap-4 py-3 transition hover:bg-stone-50">
                <span class="w-8 shrink-0 font-display text-sm font-semibold text-stone-300 transition group-hover:text-[#8a6d1b]">03</span>
                <span class="flex-1 font-medium text-stone-800">Tambah Video YouTube</span>
            </a>
            <a href="{{ route('admin.users.create') }}" class="group flex items-baseline gap-4 py-3 transition hover:bg-stone-50">
                <span class="w-8 shrink-0 font-display text-sm font-semibold text-stone-300 transition group-hover:text-[#8a6d1b]">04</span>
                <span class="flex-1 font-medium text-stone-800">Tambah Pengguna <span class="font-normal text-stone-400">— siswa / guru</span></span>
            </a>
            <a href="{{ route('admin.users.import-form') }}" class="group flex items-baseline gap-4 py-3 transition hover:bg-stone-50">
                <span class="w-8 shrink-0 font-display text-sm font-semibold text-stone-300 transition group-hover:text-[#8a6d1b]">05</span>
                <span class="flex-1 font-medium text-stone-800">Import Siswa <span class="font-normal text-stone-400">— dari file CSV</span></span>
            </a>
            <a href="{{ route('profile.edit') }}" class="group flex items-baseline gap-4 py-3 transition hover:bg-stone-50">
                <span class="w-8 shrink-0 font-display text-sm font-semibold text-stone-300 transition group-hover:text-[#8a6d1b]">06</span>
                <span class="flex-1 font-medium text-stone-800">Profil Saya <span class="font-normal text-stone-400">— ganti username & password</span></span>
            </a>
        </div>
</div>
@endsection
