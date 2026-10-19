<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0e1b33">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>@yield('title', 'Kelola') — E-Library Sekolah</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=source-serif-4:600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream font-sans text-stone-900 antialiased">
<div class="min-h-screen lg:flex" x-data="{ sidebarOpen: false }">
    {{-- Sidebar desktop --}}
    <aside class="hidden lg:flex lg:w-72 lg:flex-col lg:fixed lg:inset-y-0 bg-gradient-to-b from-[#101f42] via-[#0e1b33] to-[#080f22] text-white shadow-lux">
        <div class="pointer-events-none absolute -top-16 right-0 h-48 w-48 rounded-full bg-[#c9a227]/10 blur-3xl" aria-hidden="true"></div>
        <div class="relative flex h-16 items-center gap-2.5 px-5">
            <span class="flex items-center justify-center rounded-xl bg-white/[0.07] p-1 ring-1 ring-white/15"><x-school-logo size="sm" /></span>
            <a href="{{ route('admin.dashboard') }}" class="leading-tight">
                <span class="block font-display text-base font-semibold tracking-tight text-white">E-Library</span>
                <span class="block text-[10px] font-bold uppercase tracking-[0.24em] text-[#d9c27a]">Admin</span>
            </a>
        </div>
        <nav class="flex-1 space-y-1 p-4">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'side-link-active' : 'side-link' }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                Dashboard
            </a>
            <div class="pt-3">
                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Konten</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.ebooks.index') }}" class="{{ request()->routeIs('admin.ebooks.*') ? 'side-link-active' : 'side-link' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 2h7v20H6a2 2 0 0 1-2-2V4a2 2 0 0 1 0-2Zm9 0h5a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-5V2Z"/></svg>
                        E-Book
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'side-link-active' : 'side-link' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 5a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5Z"/></svg>
                        Kategori
                    </a>
                    <a href="{{ route('admin.videos.index') }}" class="{{ request()->routeIs('admin.videos.*') ? 'side-link-active' : 'side-link' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2 7a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7Zm13 .6l7-4v13l-7-4V7.6Z"/></svg>
                        Video
                    </a>
                </div>
            </div>
            <div class="pt-3">
                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Pengguna</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'side-link-active' : 'side-link' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0H2.5Z"/><circle cx="17" cy="9" r="2.8"/><path d="M15.5 14.7a5.8 5.8 0 0 1 6 5.3h-4.6a6.6 6.6 0 0 0-1.4-5.3Z"/></svg>
                        Data Pengguna
                    </a>
                </div>
            </div>
            <div class="pt-3">
                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Akun</p>
                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'side-link-active' : 'side-link' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M4 21a8 8 0 0 1 16 0H4Z"/></svg>
                        Profil Saya
                    </a>
                </div>
            </div>
            <div class="pt-3">
                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Pratinjau</p>
                <a href="{{ route('ebooks.index') }}" class="side-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" fill-rule="evenodd" aria-hidden="true"><path d="M12 6c4.5 0 8.4 2.6 10 6-1.6 3.4-5.5 6-10 6S3.6 15.4 2 12c1.6-3.4 5.5-6 10-6Zm0 2C8.7 8 5.7 9.9 4 12c1.7 2.1 4.7 4 8 4s6.3-1.9 8-4c-1.7-2.1-4.7-4-8-4Zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg>
                    Lihat sebagai Siswa
                </a>
            </div>
        </nav>
        <div class="border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#c9a227] text-base font-bold text-[#1a1405]">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[#d9c27a]">Administrator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="w-full rounded-xl border border-white/15 px-3 py-2 text-left text-sm text-stone-300 transition hover:bg-white/10 hover:text-white">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Mobile sidebar --}}
    <div x-show="sidebarOpen" style="display: none;" @keydown.escape.window="sidebarOpen = false" class="fixed inset-0 z-40 lg:hidden">
        <div x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-stone-950/60" @click="sidebarOpen = false"></div>
        <aside x-show="sidebarOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-x-8"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-8"
            class="absolute left-0 top-0 h-full w-72 max-w-[85vw] overflow-y-auto rounded-r-2xl bg-[#0e1b33] p-4 text-white shadow-lift">
            <div class="flex items-center justify-between">
                <p class="flex items-center gap-2 font-display text-lg font-bold"><x-school-logo size="xs" /> E-Library</p>
                <button @click="sidebarOpen = false" class="rounded-lg border border-white/15 px-3.5 py-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-stone-300 transition hover:bg-white/10 hover:text-white">Tutup</button>
            </div>
            <nav class="mt-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'side-link-active' : 'side-link' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Dashboard
                </a>
                <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Konten</p>
                <a href="{{ route('admin.ebooks.index') }}" class="{{ request()->routeIs('admin.ebooks.*') ? 'side-link-active' : 'side-link' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 2h7v20H6a2 2 0 0 1-2-2V4a2 2 0 0 1 0-2Zm9 0h5a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-5V2Z"/></svg>
                    E-Book
                </a>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'side-link-active' : 'side-link' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 5a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5Z"/></svg>
                    Kategori
                </a>
                <a href="{{ route('admin.videos.index') }}" class="{{ request()->routeIs('admin.videos.*') ? 'side-link-active' : 'side-link' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2 7a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V7Zm13 .6l7-4v13l-7-4V7.6Z"/></svg>
                    Video
                </a>
                @if(auth()->user()->isAdmin())
                    <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Pengguna</p>
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'side-link-active' : 'side-link' }}">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0H2.5Z"/><circle cx="17" cy="9" r="2.8"/><path d="M15.5 14.7a5.8 5.8 0 0 1 6 5.3h-4.6a6.6 6.6 0 0 0-1.4-5.3Z"/></svg>
                        Data Pengguna
                    </a>
                @endif
                <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Akun</p>
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'side-link-active' : 'side-link' }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path d="M4 21a8 8 0 0 1 16 0H4Z"/></svg>
                    Profil Saya
                </a>
                <p class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-widest text-stone-500">Pratinjau</p>
                <a href="{{ route('ebooks.index') }}" class="side-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" fill-rule="evenodd" aria-hidden="true"><path d="M12 6c4.5 0 8.4 2.6 10 6-1.6 3.4-5.5 6-10 6S3.6 15.4 2 12c1.6-3.4 5.5-6 10-6Zm0 2C8.7 8 5.7 9.9 4 12c1.7 2.1 4.7 4 8 4s6.3-1.9 8-4c-1.7-2.1-4.7-4-8-4Zm0 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg>
                    Lihat sebagai Siswa
                </a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button class="w-full rounded-xl border border-white/15 px-3 py-2 text-sm">Keluar</button>
            </form>
        </aside>
    </div>

    <div class="flex-1 lg:pl-72">
        {{-- Topbar --}}
        <header class="sticky top-0 z-10 border-b border-stone-200/70 bg-[#faf7f0]/85 backdrop-blur">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6">
                <button @click="sidebarOpen = true" class="btn-ghost !px-3.5 !py-2 lg:hidden"><span class="text-[11px] font-semibold uppercase tracking-[0.2em]">Menu</span></button>
                <div>
                    <p class="eyebrow">Panel Kelola</p>
                    <h1 class="title-display text-xl leading-tight">@yield('title', 'Kelola')</h1>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl p-4 sm:p-6">
            @if(session('success'))
                <div class="flash-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash-error">{{ session('error') }}</div>
            @endif
            @if(session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent']))
                <div class="flash-success">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="flash-error">
                    <ul class="list-disc ps-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
{{-- Bilah progres + prefetch agar pindah halaman terasa instan --}}
@include('layouts.partials.page-progress')
</body>
</html>
