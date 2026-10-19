@extends('layouts.admin')
@section('title', 'Edit Pengguna')

@section('content')
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="card card-pad max-w-xl space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="label">Peran *</label>
        <select id="role" name="role" required class="input">
            <option value="siswa" @selected(old('role', $user->role) === 'siswa')>Siswa — login via NIS</option>
            <option value="guru" @selected(old('role', $user->role) === 'guru')>Guru — login via username</option>
        </select>
    </div>
    <div>
        <label class="label">Nama *</label>
        <input name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="input">
    </div>

    {{-- Khusus siswa --}}
    <div id="fields-siswa" class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="label">NIS *</label>
            <input name="nis" value="{{ old('nis', $user->nis) }}" maxlength="20" class="input">
        </div>
        <div>
            <label class="label">Kelas <span class="font-normal text-stone-400">(opsional)</span></label>
            <input name="class" value="{{ old('class', $user->class) }}" maxlength="50" class="input">
        </div>
    </div>

    {{-- Khusus guru --}}
    <div id="fields-guru" class="hidden grid gap-4 sm:grid-cols-2">
        <div>
            <label class="label">Username *</label>
            <input name="username" value="{{ old('username', $user->username) }}" maxlength="50" class="input">
        </div>
        <div>
            <label class="label">Password @if($user->password)<span class="font-normal text-stone-400">(kosongkan jika tidak ganti)</span>@else<span class="font-normal text-red-600">* (belum ada — wajib diisi)</span>@endif</label>
            <input type="password" name="password" maxlength="255" autocomplete="new-password" class="input" data-has-password="{{ $user->password ? '1' : '0' }}">
        </div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('admin.users.index', ['role' => $user->role]) }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Perbarui</button>
    </div>
</form>

<script>
(function () {
    const role = document.getElementById('role');
    const siswa = document.getElementById('fields-siswa');
    const guru = document.getElementById('fields-guru');
    if (!role) return;
    const nis = siswa.querySelector('[name="nis"]');
    const kelas = siswa.querySelector('[name="class"]');
    const username = guru.querySelector('[name="username"]');
    const password = guru.querySelector('[name="password"]');
    const hasPassword = password.dataset.hasPassword === '1';
    function sync() {
        const isGuru = role.value === 'guru';
        siswa.classList.toggle('hidden', isGuru);
        siswa.classList.toggle('grid', !isGuru);
        guru.classList.toggle('hidden', !isGuru);
        guru.classList.toggle('grid', isGuru);
        nis.required = !isGuru;
        kelas.required = false;
        username.required = isGuru;
        password.required = isGuru && !hasPassword;
    }
    role.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
