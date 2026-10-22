<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-ghost shrink-0 !px-3 !py-1.5 !text-xs">← Dashboard</a>
            <div class="min-w-0">
                <p class="eyebrow">Kontribusi Guru</p>
                <h2 class="title-display text-xl">Tambah Video YouTube</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('guru.videos.store') }}" class="card card-pad space-y-4">
                @csrf
                <p class="rounded-xl bg-amber-50 p-3 text-sm text-stone-600">Video yang Anda tambahkan akan tercatat sebagai kontribusi <strong>{{ auth()->user()->name }}</strong>. Kuota Anda: <strong>{{ $used ?? 0 }}/10</strong>.</p>
                @if(session('error'))
                    <p class="rounded-xl bg-red-50 p-3 text-sm font-semibold text-red-700">{{ session('error') }}</p>
                @endif
                <div>
                    <label class="label">E-Book terkait *</label>
                    <select name="ebook_id" required class="input">
                        <option value="">— Pilih e-book —</option>
                        @foreach($ebooks as $ebook)
                            <option value="{{ $ebook->id }}" @selected((string)old('ebook_id', $selectedEbookId ?? '') === (string)$ebook->id)>{{ $ebook->title }}</option>
                        @endforeach
                    </select>
                    @error('ebook_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Judul video *</label>
                    <input name="title" value="{{ old('title') }}" required maxlength="255" class="input">
                    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">URL YouTube *</label>
                    <input name="youtube_url" value="{{ old('youtube_url') }}" required maxlength="255" placeholder="https://www.youtube.com/watch?v=..." class="input">
                    @error('youtube_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label">Deskripsi</label>
                    <textarea name="description" rows="3" class="input">{{ old('description') }}</textarea>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn-ghost">Batal</a>
                    <button class="btn-primary">Simpan Kontribusi</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
