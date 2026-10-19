<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0e1b33">
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <title>{{ config('app.name', 'E-Library SMKN 1 Dukuhturi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=source-serif-4:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f6f3ea] font-sans text-stone-900 antialiased">
        <div class="relative min-h-screen">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(720px_280px_at_50%_-80px,rgba(201,162,39,0.14),transparent_70%),radial-gradient(640px_260px_at_85%_0px,rgba(29,79,215,0.08),transparent_65%)]" aria-hidden="true"></div>
            <div class="relative">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <div class="border-b border-stone-200/70 bg-white/80 backdrop-blur">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <!-- Page Content -->
            <main>
                @if(session('success') || session('error') || session('status'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                        @if(session('success'))
                            <div class="flash-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="flash-error">{{ session('error') }}</div>
                        @endif
                        @if(session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent']))
                            <div class="flash-success">{{ session('status') }}</div>
                        @endif
                    </div>
                @endif
                {{ $slot }}
            </main>

            <footer class="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6 lg:px-8">
                <p class="font-display text-sm font-semibold tracking-wide text-stone-700">E-Library &middot; SMKN 1 Dukuhturi</p>
            </footer>
            </div>
        </div>

        {{-- Asisten belajar mengambang — selalu tersedia --}}
        @auth
            @include('chatbot._floating')
        @endauth

        {{-- Bilah progres + prefetch agar pindah halaman terasa instan --}}
        @include('layouts.partials.page-progress')
    </body>
</html>
