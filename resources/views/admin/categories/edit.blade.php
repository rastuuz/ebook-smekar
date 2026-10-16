@extends('layouts.admin')
@section('title', 'Edit Kategori')

@section('content')
<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card card-pad max-w-xl space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="label">Nama *</label>
        <input name="name" value="{{ old('name', $category->name) }}" required maxlength="100" class="input">
    </div>
    <div>
        <label class="label">Deskripsi</label>
        <textarea name="description" rows="3" maxlength="1000" class="input">{{ old('description', $category->description) }}</textarea>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Perbarui</button>
    </div>
</form>
@endsection
