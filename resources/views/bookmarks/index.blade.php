<x-app-layout>
    <x-slot name="title">Simpanan Saya</x-slot>
    <x-slot name="header">
        <p class="eyebrow">Rak Pribadi &middot; Simpanan</p>
        <h2 class="title-display mt-2 text-2xl sm:text-[1.7rem]">Simpanan Saya</h2>
        <p class="mt-1 max-w-xl text-sm text-stone-500">E-book yang kamu tandai untuk dibaca nanti.</p>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div><x-back-button :href="route('dashboard')" label="Dashboard" /></div>
            @if($bookmarks->isEmpty())
                <div class="card card-pad py-12 text-center">
                    <p class="title-display text-lg">Belum ada simpanan</p>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">Buka detail e-book lalu tekan “Simpan” untuk menambah ke rak ini.</p>
                    <a href="{{ route('ebooks.index') }}" class="btn-primary mt-4 inline-flex">Jelajahi Katalog →</a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($bookmarks as $bookmark)
                        @php($ebook = $bookmark->ebook)
                        @if($ebook)
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
                                    <p class="mt-2 text-xs text-stone-400">Disimpan {{ $bookmark->created_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="mt-auto flex gap-2 p-4 sm:p-5 sm:pt-3">
                                <a href="{{ route('ebooks.show', $ebook) }}" class="btn-primary flex-1 !py-2 !text-[13px]">Lihat Detail →</a>
                                <form method="POST" action="{{ route('bookmarks.toggle', $ebook) }}">
                                    @csrf
                                    <button class="btn-ghost !py-2 !text-[13px]" onclick="return confirm('Hapus dari simpanan?')">Hapus</button>
                                </form>
                            </div>
                        </article>
                        @endif
                    @endforeach
                </div>
                <div class="mt-4">{{ $bookmarks->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
