<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-ghost shrink-0 !px-3 !py-1.5 !text-xs">← Dashboard</a>
            <div class="min-w-0">
                <p class="eyebrow">Kontribusi Guru</p>
                <h2 class="title-display text-xl">Tambah E-Book</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('guru.ebooks.store') }}" enctype="multipart/form-data" class="card card-pad space-y-4">
                @csrf
                <p class="rounded-xl bg-amber-50 p-3 text-sm text-stone-600">E-book yang Anda tambahkan akan tercatat sebagai kontribusi <strong>{{ auth()->user()->name }}</strong> dan tampil di halaman detail. Kuota Anda: <strong>{{ $used ?? 0 }}/10</strong> (file PDF maks 5 MB).</p>
                @if(session('error'))
                    <p class="rounded-xl bg-red-50 p-3 text-sm font-semibold text-red-700">{{ session('error') }}</p>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="label">Judul *</label>
                        <input name="title" value="{{ old('title') }}" required maxlength="255" class="input">
                        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Penulis *</label>
                        <input name="author" value="{{ old('author') }}" required maxlength="255" class="input">
                        @error('author')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Kategori *</label>
                        <select name="category_id" required class="input">
                            <option value="">— Pilih —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Deskripsi</label>
                        <textarea name="description" rows="4" class="input">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="label">Cover <span class="font-normal text-stone-400">(jpg/png/webp, maks 2MB)</span></label>
                        <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full text-sm">
                        @error('cover')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                            <label class="label">File PDF <span class="font-normal text-stone-400">(maks 5MB)</span></label>
                        <input type="file" name="file" accept=".pdf" class="mt-1 w-full text-sm">
                        <p class="hint">Teks PDF otomatis dibaca untuk chatbot.</p>
                        @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dashboard') }}" class="btn-ghost">Batal</a>
                    <button class="btn-primary">Simpan Kontribusi</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
