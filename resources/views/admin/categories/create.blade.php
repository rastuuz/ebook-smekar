@extends('layouts.admin')
@section('title', 'Tambah Kategori')

@section('content')
<form method="POST" action="{{ route('admin.categories.store') }}" class="card card-pad max-w-xl space-y-4">
    @csrf
    <div>
        <label class="label">Nama *</label>
        <input name="name" value="{{ old('name') }}" required maxlength="100" class="input" placeholder="Contoh: Informatika">
    </div>
    <div>
        <label class="label">Deskripsi</label>
        <textarea name="description" rows="3" maxlength="1000" class="input" placeholder="Deskripsi singkat…">{{ old('description') }}</textarea>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Simpan</button>
    </div>
</form>
@endsection
