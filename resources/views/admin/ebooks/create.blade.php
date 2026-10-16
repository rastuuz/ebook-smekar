@extends('layouts.admin')
@section('title', 'Tambah E-Book')

@section('content')
<form method="POST" action="{{ route('admin.ebooks.store') }}" enctype="multipart/form-data" class="card card-pad max-w-2xl space-y-4">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="label">Judul *</label>
            <input name="title" value="{{ old('title') }}" required maxlength="255" class="input">
        </div>
        <div>
            <label class="label">Penulis *</label>
            <input name="author" value="{{ old('author') }}" required maxlength="255" class="input">
        </div>
        <div>
            <label class="label">Kategori *</label>
            <select name="category_id" required class="input">
                <option value="">— Pilih —</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="label">Deskripsi</label>
            <textarea name="description" rows="4" class="input">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="label">Cover <span class="font-normal text-stone-400">(jpg/png/webp, maks 2MB)</span></label>
            <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full text-sm">
        </div>
        <div>
            <label class="label">File PDF <span class="font-normal text-stone-400">(maks 30MB)</span></label>
            <input type="file" name="file" accept=".pdf" class="mt-1 w-full text-sm">
            <p class="hint">Teks PDF otomatis dibaca untuk chatbot.</p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.ebooks.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Simpan</button>
    </div>
</form>
@endsection
