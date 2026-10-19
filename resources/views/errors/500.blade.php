<x-guest-layout>
    <div class="py-6 text-center">
        <p class="font-display text-6xl font-bold text-stone-200">500</p>
        <h1 class="title-display mt-2 text-xl">Terjadi kesalahan</h1>
        <p class="mx-auto mt-1 max-w-xs text-sm text-stone-500">Server mengalami gangguan sesaat. Silakan coba lagi — jika masih terjadi, hubungi admin.</p>
        <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
            <a href="{{ route('dashboard') }}" class="btn-primary">Ke Dashboard</a>
            <a href="{{ route('ebooks.index') }}" class="btn-ghost">Katalog E-Book</a>
        </div>
    </div>
</x-guest-layout>
