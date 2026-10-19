<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0a1430">
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=source-serif-4:600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#0a1430] font-sans text-stone-900 antialiased">
        <div class="relative flex min-h-screen flex-col items-center overflow-hidden bg-gradient-to-b from-[#101f42] via-[#0a1430] to-[#070f24] px-4 py-10 sm:justify-center sm:py-14">
            <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-brand-600/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-24 h-96 w-96 rounded-full bg-gold-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.35] [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:22px_22px] [mask-image:radial-gradient(480px_320px_at_50%_20%,black,transparent)]" aria-hidden="true"></div>

            <a href="/" class="relative flex items-center gap-3 rounded-2xl px-3 py-1.5 transition hover:bg-white/5">
                <span class="flex items-center justify-center rounded-xl bg-gradient-to-b from-white/15 to-white/5 p-1.5 ring-1 ring-white/20 shadow-lux"><x-school-logo size="md" /></span>
                <span class="leading-tight">
                    <span class="block font-display text-2xl font-bold tracking-tight text-white">E-Library</span>
                    <span class="block text-[11px] font-bold uppercase tracking-[0.28em] text-gold-300">SMKN 1 Dukuhturi</span>
                </span>
            </a>

            <div class="card relative mt-8 w-full p-6 shadow-lux sm:max-w-md sm:p-8">
                {{ $slot }}
            </div>

            <p class="relative mt-6 text-center text-[11px] font-medium uppercase tracking-[0.2em] text-stone-400">E-Library SMKN 1 Dukuhturi</p>
        </div>
        {{-- Bilah progres + prefetch agar pindah halaman terasa instan --}}
        @include('layouts.partials.page-progress')
    </body>
</html>
