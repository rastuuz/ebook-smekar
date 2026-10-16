@extends('layouts.admin')
@section('title', 'Video Pembelajaran')

@section('content')
<div class="mb-4 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
    <p class="text-sm text-stone-500">{{ $videos->total() }} video • embed YouTube aman (nocookie)</p>
    <a href="{{ route('admin.videos.create') }}" class="btn-primary !py-2 text-center text-sm">+ Tambah Video</a>
</div>

@if($videos->isEmpty())
    <div class="card card-pad py-12 text-center text-sm text-stone-500">Belum ada video.</div>
@else
    <div class="table-wrap">
        <div class="table-scroll">
            <table class="table-base min-w-[640px]">
                <thead class="table-head">
                    <tr><th class="px-4 py-3">Judul</th><th class="px-4 py-3">E-Book</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($videos as $video)
                    <tr class="transition hover:bg-stone-50">
                        <td class="px-4 py-3"><p class="font-semibold">{{ $video->title }}</p><p class="max-w-[280px] truncate text-xs text-stone-400">{{ $video->youtube_url }}</p></td>
                        <td class="px-4 py-3 text-xs text-stone-500">{{ $video->ebook->title ?? '-' }}</td>
                        <td class="px-4 py-3"><div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('admin.videos.edit', $video) }}" class="btn-row">Edit</a>
                            <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" onsubmit="return confirm('Hapus video?')">@csrf @method('DELETE')
                                <button class="btn-danger">Hapus</button>
                            </form>
                        </div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $videos->links() }}</div>
@endif
@endsection
