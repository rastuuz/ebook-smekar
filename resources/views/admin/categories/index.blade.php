@extends('layouts.admin')
@section('title', 'Kategori')

@section('content')
<div class="mb-3"><x-back-button :href="route('admin.dashboard')" label="Dashboard" /></div>
<div class="mb-4 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
    <p class="text-sm text-stone-500">{{ $categories->total() }} kategori • hanya bisa dihapus jika tidak dipakai e-book</p>
    <a href="{{ route('admin.categories.create') }}" class="btn-primary !py-2 text-center text-sm">+ Tambah Kategori</a>
</div>

@if($categories->isEmpty())
    <div class="card card-pad py-12 text-center text-sm text-stone-500">Belum ada kategori.</div>
@else
    <div class="table-wrap">
        <div class="table-scroll">
            <table class="table-base">
                <thead class="table-head">
                    <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3 text-center">E-Book</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($categories as $category)
                    <tr class="transition hover:bg-stone-50">
                        <td class="px-4 py-3"><p class="font-semibold">{{ $category->name }}</p><p class="line-clamp-1 text-xs text-stone-400">{{ $category->description }}</p></td>
                        <td class="px-4 py-3 text-center"><span class="badge-num">{{ $category->ebooks_count }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn-row">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
@endif
@endsection
