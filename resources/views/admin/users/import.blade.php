@extends('layouts.admin')
@section('title', 'Import Pengguna')

@section('content')
<div class="card card-pad max-w-2xl">
    <p class="eyebrow">Data Pengguna &middot; Import</p>
    <h2 class="title-display mt-2 text-xl">Import Siswa / Guru dari CSV / Excel</h2>
    <p class="mt-1 text-sm text-stone-500">Bisa upload langsung file Excel (<code class="font-mono">.xlsx</code>) — tidak perlu convert ke CSV. Siswa login via NIS tanpa password. Guru login via username + sandi — pakai <strong>sandi default yang sama</strong> untuk semua, nanti guru ganti sendiri via menu Profil.</p>

    <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
        @csrf
        <div>
            <label for="role" class="text-sm font-semibold text-stone-700">Peran *</label>
            <select id="role" name="role" required class="input mt-1">
                <option value="siswa" @selected(old('role', request('role')) === 'siswa' || old('role', request('role')) === null)>Siswa — kolom: NIS, Nama (Kelas opsional)</option>
                <option value="guru" @selected(old('role', request('role')) === 'guru')>Guru — kolom: Username, Nama</option>
            </select>
            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="help-siswa" class="rounded-xl bg-stone-50 p-3 text-sm text-stone-500">
            Excel/CSV: <strong>semua sheet</strong> dibaca (kalau file 1 sheet per kelas, semua ikut masuk), baris pertama tiap sheet header — contoh: <code class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-xs">NIS | Nama</code> atau <code class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-xs">NIS | Nama | Kelas</code> (urutan bebas, kolom Kelas opsional, boleh ada kolom tambahan seperti No). Varian header dikenali: NIS/NISN/No Induk, Nama/Nama Lengkap, Kelas/Rombel. Contoh isi: <code class="rounded bg-stone-100 px-1.5 py-0.5 font-mono text-xs">18038 | Ahmad Dani</code>. NIS yang sama akan diperbarui. Tips: format kolom NIS sebagai <strong>Text</strong> di Excel agar angka tidak berubah.
        </div>
        <div id="help-guru" class="hidden rounded-xl bg-amber-50 p-3 text-sm text-stone-600">
            Excel/CSV: sheet pertama, baris pertama header — contoh: <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">Username | Nama</code> (boleh juga <code class="rounded bg-white px-1.5 py-0.5 font-mono text-xs">NIP | Nama</code> — NIP dipakai sebagai username). Opsional kolom Sandi/Password untuk sandi khusus per baris; jika kosong dipakai sandi default di bawah. Username yang sama akan diperbarui namanya — <strong>password yang sudah diganti guru tidak ditimpa</strong> kecuali centang reset.
        </div>

        <div id="fields-guru" class="hidden space-y-3 rounded-xl border border-amber-200 bg-amber-50/50 p-3">
            <div>
                <label for="default_password" class="text-sm font-semibold text-stone-700">Sandi default untuk semua guru * <span class="font-normal text-stone-400">(min 8)</span></label>
                <input id="default_password" type="text" name="default_password" value="{{ old('default_password', 'guru12345') }}" maxlength="255" autocomplete="off" class="input mt-1 font-mono">
                <p class="mt-1 text-xs text-stone-500">Contoh: <code class="font-mono">guru12345</code>. Bagikan ke guru, minta mereka login di halaman Login Guru lalu ganti via Profil.</p>
                @error('default_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <label class="flex items-start gap-2 text-sm text-stone-600">
                <input type="checkbox" name="reset_password" value="1" @checked(old('reset_password')) class="mt-1">
                <span>Reset juga password guru yang sudah ada ke sandi default ini (pakai hanya jika guru lupa sandi / mau samakan ulang).</span>
            </label>
        </div>

        <div>
            <label for="file" class="text-sm font-semibold text-stone-700">File CSV / Excel (maks 5 MB)</label>
            <input id="file" type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required class="input mt-1">
            @error('file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <button class="btn-primary">Import Sekarang</button>
            <a href="{{ route('admin.users.index', ['role' => request('role', 'siswa')]) }}" class="btn-ghost text-center">Kembali</a>
        </div>
    </form>

    <div class="mt-5 rounded-xl bg-stone-50 p-4 text-sm text-stone-500">
        <p class="font-semibold text-stone-700">Contoh Excel siswa (boleh multi-sheet, tiap sheet ada header):</p>
        <pre class="mt-1 overflow-x-auto font-mono text-xs leading-relaxed">NIS       | Nama
18038     | Ahmad Dani
18039     | Rina Wulandari</pre>
        <p class="mt-3 font-semibold text-stone-700">Contoh Excel guru (Sheet1):</p>
        <pre class="mt-1 overflow-x-auto font-mono text-xs leading-relaxed">Username    | Nama
budi.santoso| Budi Santoso
siti.aisyah | Siti Aisyah</pre>
        <p class="mt-2 text-xs">File CSV tetap didukung (pemisah koma <code class="font-mono">,</code> atau titik-koma <code class="font-mono">;</code> otomatis dikenali).</p>
    </div>
</div>

<script>
(function () {
    const role = document.getElementById('role');
    const helpSiswa = document.getElementById('help-siswa');
    const helpGuru = document.getElementById('help-guru');
    const fieldsGuru = document.getElementById('fields-guru');
    const defaultPassword = document.getElementById('default_password');
    if (!role) return;
    function sync() {
        const isGuru = role.value === 'guru';
        helpSiswa.classList.toggle('hidden', isGuru);
        helpGuru.classList.toggle('hidden', !isGuru);
        fieldsGuru.classList.toggle('hidden', !isGuru);
        if (defaultPassword) defaultPassword.required = isGuru;
    }
    role.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
