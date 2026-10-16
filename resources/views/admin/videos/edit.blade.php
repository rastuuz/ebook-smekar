@extends('layouts.admin')
@section('title', 'Edit Video')

@section('content')
<form method="POST" action="{{ route('admin.videos.update', $video) }}" class="card card-pad max-w-xl space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="label">E-Book *</label>
        <select name="ebook_id" required class="input">
            @foreach($ebooks as $ebook)
                <option value="{{ $ebook->id }}" @selected(old('ebook_id', $video->ebook_id) == $ebook->id)>{{ $ebook->title }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label">Judul Video *</label>
        <input name="title" value="{{ old('title', $video->title) }}" required class="input">
    </div>
    <div>
        <label class="label">URL YouTube *</label>
        <input name="youtube_url" value="{{ old('youtube_url', $video->youtube_url) }}" required type="url" class="input">
    </div>
    <div>
        <label class="label">Deskripsi</label>
        <textarea name="description" rows="3" class="input">{{ old('description', $video->description) }}</textarea>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.videos.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Perbarui</button>
    </div>
</form>
@endsection
