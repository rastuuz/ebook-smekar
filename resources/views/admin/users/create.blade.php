@extends('layouts.admin')
@section('title', 'Tambah Pengguna')

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="card card-pad max-w-xl space-y-4">
    @csrf
    <div>
        <label class="label">Peran *</label>
        <select id="role" name="role" required class="input">
            <option value="siswa" @selected(old('role', 'siswa') === 'siswa')>Siswa — login via NIS</option>
            <option value="guru" @selected(old('role') === 'guru')>Guru — login via username</option>
        </select>
    </div>
    <div>
        <label class="label">Nama *</label>
        <input name="name" value="{{ old('name') }}" required maxlength="255" class="input">
    </div>

    {{-- Khusus siswa --}}
    <div id="fields-siswa" class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="label">NIS *</label>
            <input name="nis" value="{{ old('nis') }}" maxlength="20" class="input">
        </div>
        <div>
            <label class="label">Kelas <span class="font-normal text-stone-400">(opsional)</span></label>
            <input name="class" value="{{ old('class') }}" maxlength="50" placeholder="Contoh: XII IPA 1" class="input">
        </div>
    </div>

    {{-- Khusus guru --}}
    <div id="fields-guru" class="hidden grid gap-4 sm:grid-cols-2">
        <div>
            <label class="label">Username *</label>
            <input name="username" value="{{ old('username') }}" maxlength="50" placeholder="Contoh: guru" class="input">
        </div>
        <div>
            <label class="label">Password * <span class="font-normal text-stone-400">(min 8)</span></label>
            <input type="password" name="password" maxlength="255" autocomplete="new-password" class="input">
        </div>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('admin.users.index') }}" class="btn-ghost">Batal</a>
        <button class="btn-primary">Simpan</button>
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
    function sync() {
        const isGuru = role.value === 'guru';
        siswa.classList.toggle('hidden', isGuru);
        siswa.classList.toggle('grid', !isGuru);
        guru.classList.toggle('hidden', !isGuru);
        guru.classList.toggle('grid', isGuru);
        nis.required = !isGuru;
        kelas.required = false;
        username.required = isGuru;
        password.required = isGuru;
    }
    role.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
