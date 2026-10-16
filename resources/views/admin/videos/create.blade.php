@extends('layouts.admin')
@section('title', 'Tambah Video')

@section('content')
<form method="POST" action="{{ route('admin.videos.store') }}" class="card card-pad max-w-xl space-y-4">
    @csrf
    <div>
        <label class="label">E-Book *</label>
        <select name="ebook_id" required class="input">
            <option value="">— Pilih —</option>
            @foreach($ebooks as $ebook)
                <option value="{{ $ebook->id }}" @selected(old('ebook_id', $selectedEbookId ?? null) == $ebook->id)>{{ $ebook->title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Judul Video *</label>
        <input name="title" value="{{ old('title') }}" required class="input" placeholder="Contoh: Materi Energi">
    </div>
    <div>
        <label class="label">URL YouTube *</label>
        <input name="youtube_url" value="{{ old('youtube_url') }}" required type="url" class="input" placeholder="https://www.youtube.com/watch?v=…">
        <p class="hint">Mendukung watch, youtu.be, embed, shorts. Tidak mengunduh video.</p>
    </div>
    <div>
        <label class="label">Deskripsi</label>
        <textarea name="description" rows="3" class="input">{{ old('description') }}</textarea>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.videos.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Simpan</button>
    </div>
</form>
@endsection
