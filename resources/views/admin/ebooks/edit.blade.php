@extends('layouts.admin')
@section('title', 'Edit E-Book')

@section('content')
<form method="POST" action="{{ route('admin.ebooks.update', $ebook) }}" enctype="multipart/form-data" class="card card-pad max-w-2xl space-y-4">
    @csrf @method('PUT')
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="label">Judul *</label>
            <input name="title" value="{{ old('title', $ebook->title) }}" required class="input">
        </div>
        <div>
            <label class="label">Penulis *</label>
            <input name="author" value="{{ old('author', $ebook->author) }}" required class="input">
        </div>
        <div>
            <label class="label">Kategori *</label>
            <select name="category_id" required class="input">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $ebook->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="label">Deskripsi</label>
            <textarea name="description" rows="4" class="input">{{ old('description', $ebook->description) }}</textarea>
        </div>
        <div>
            <label class="label">Cover baru <span class="font-normal text-stone-400">(kosongkan jika tidak ganti)</span></label>
            <input type="file" name="cover" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full text-sm">
            @if($ebook->cover)<p class="hint">Saat ini: {{ basename($ebook->cover) }}</p>@endif
        </div>
        <div>
            <label class="label">PDF baru <span class="font-normal text-stone-400">(kosongkan jika tidak ganti)</span></label>
            <input type="file" name="file" accept=".pdf" class="mt-1 w-full text-sm">
            @if($ebook->file)<p class="hint">Saat ini: {{ basename($ebook->file) }}{{ $ebook->hasIndexedContent() ? ' • teks sudah terbaca' : '' }}</p>@endif
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.ebooks.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Perbarui</button>
    </div>
</form>
@endsection
