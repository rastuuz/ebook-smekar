@extends('layouts.admin')
@section('title', 'Data Pengguna')

@section('content')
<div class="mb-3"><x-back-button :href="route('admin.dashboard')" label="Dashboard" /></div>
<form method="GET" action="{{ route('admin.users.index') }}" class="card mb-4 flex flex-col gap-2 p-3 sm:flex-row sm:items-center">
    <input type="hidden" name="role" value="{{ $role }}">
    <input type="search" name="q" value="{{ $search ?? '' }}" autocomplete="off" placeholder="{{ $role === \App\Models\User::ROLE_GURU ? 'Cari nama / NIP (username)…' : 'Cari nama / NIS…' }}" class="input w-full !mt-0 sm:max-w-xs" aria-label="Cari pengguna">
    <p class="text-sm text-stone-500 sm:ml-1">{{ $role === \App\Models\User::ROLE_GURU ? 'Guru login via NIP (username)' : 'Siswa login via NIS' }}</p>
    <div class="flex gap-2 sm:ml-auto">
        @if(! empty($search ?? ''))
            <a href="{{ route('admin.users.index', ['role' => $role]) }}" class="btn-ghost !py-2 text-sm">Atur ulang</a>
        @endif
        <a href="{{ route('admin.users.import-form') }}" class="btn-ghost !py-2 text-center text-sm">Import CSV</a>
        <a href="{{ route('admin.users.create') }}" class="btn-primary !py-2 text-center text-sm">+ Tambah Pengguna</a>
    </div>
</form>

<div class="-mx-4 flex flex-nowrap items-center gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0 sm:pb-0">
    <a href="{{ route('admin.users.index', array_filter(['role' => \App\Models\User::ROLE_SISWA, 'q' => $search ?? null])) }}" class="chip shrink-0 {{ $role === \App\Models\User::ROLE_SISWA ? 'chip-active' : '' }}">Siswa ({{ $counts[\App\Models\User::ROLE_SISWA] }})</a>
    <a href="{{ route('admin.users.index', array_filter(['role' => \App\Models\User::ROLE_GURU, 'q' => $search ?? null])) }}" class="chip shrink-0 {{ $role === \App\Models\User::ROLE_GURU ? 'chip-active' : '' }}">Guru ({{ $counts[\App\Models\User::ROLE_GURU] }})</a>
</div>

@if($users->isEmpty())
    <div class="card card-pad mt-4 py-12 text-center text-sm text-stone-500">
        @if(! empty($search ?? ''))
            Tidak ada hasil untuk “{{ $search }}”. <a href="{{ route('admin.users.index', ['role' => $role]) }}" class="font-semibold underline underline-offset-4">Tampilkan semua</a>.
        @else
            Belum ada data pengguna.
        @endif
    </div>
@else
    <div class="table-wrap mt-4">
        <div class="table-scroll">
            <table class="table-base">
                <thead class="table-head">
                    <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Identitas</th>@if($role === \App\Models\User::ROLE_SISWA)<th class="px-4 py-3">Kelas</th>@endif<th class="px-4 py-3 text-center">Peran</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($users as $user)
                    <tr class="transition hover:bg-stone-50">
                        <td class="px-4 py-3 font-semibold">{{ $user->name }}</td>
                        <td class="px-4 py-3 font-mono text-sm text-stone-500">{{ $user->isGuru() ? '@'.$user->username : $user->nis }}</td>
                        @if($role === \App\Models\User::ROLE_SISWA)
                        <td class="px-4 py-3 text-stone-500">{{ $user->class ?? '-' }}</td>
                        @endif
                        <td class="px-4 py-3 text-center">
                            @if($user->isGuru())
                                <span class="badge-gold">Guru</span>
                            @else
                                <span class="badge-cat">Siswa</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn-row">Edit</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')
                                <button class="btn-danger">Hapus</button>
                            </form>
                        </div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
@endif
@endsection
