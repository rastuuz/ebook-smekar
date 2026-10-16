# E-Library SMKN 1 Dukuhturi

Perpustakaan digital sekolah berbasis **Laravel 12** — katalog e-book (PDF) +
video pembelajaran YouTube + asisten belajar (chatbot) + rak pribadi siswa
(simpanan & riwayat baca).

## Fitur

- **3 peran**: siswa (masuk via NIS, tanpa password), guru (username + password,
  baca saja), admin (kelola penuh).
- **Katalog e-book**: pencarian seketika (live-search), filter kategori,
  pratinjau PDF, baca PDF inline.
- **Video pembelajaran**: kelola admin, embed YouTube di detail e-book,
  halaman khusus staf (`/videos`).
- **Asisten belajar (chatbot)**: menjawab dari isi PDF (kutipan + halaman),
  toleran typo (stemming Indonesia + fuzzy). Tanpa API key pun jalan
  (`CHATBOT_PROVIDER=local`); opsional Gemini / OpenAI dengan fallback lokal.
- **Rak pribadi**: simpanan (bookmark) + riwayat baca + dashboard siswa.
- **Panel admin**: statistik (guru, video, perlu perhatian, terpopuler),
  status indeks chatbot per e-book + tombol **Indeks ulang**,
  **import siswa dari CSV**.

## Syarat

- PHP `^8.2` + ekstensi umum Laravel (mbstring, sqlite/mysql, gd/fileinfo)
- Composer, Node.js + npm
- MySQL (disarankan, mis. XAMPP/Laragon) atau SQLite

## Instalasi cepat

```bash
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Jalankan:

```bash
php artisan serve            # http://localhost:8000
# atau: composer dev         # serve + queue + pail + vite sekaligus
```

> `APP_URL` di `.env` sesuaikan dengan alamat yang dipakai
> (mis. `http://localhost:8000`).

## Akun bawaan (seeder)

| Peran | Pintu masuk | Kredensial |
|---|---|---|
| Admin | `/login-admin` | `admin` / `password` |
| Guru | `/login-guru` | `guru` / `password` |
| Siswa | `/login-siswa` | NIS `18034` (tanpa password) |

Siswa contoh: `18034`–`18037`. **Segera ganti password admin/guru setelah
instalasi** (menu Profil).

## Alur pakai

1. **Admin** (`/login-admin`): buat kategori → tambah e-book (cover + PDF) →
   tambah video YouTube → tambah/import siswa. Perhatikan badge status di
   daftar e-book: `Siap chatbot` / `Teks tak terbaca (scan?)` /
   `PDF belum ada`; gunakan **Indeks ulang** bila perlu.
2. **Siswa** (`/login-siswa`): katalog → detail → baca PDF / tonton video /
   tanya asisten → `☆ Simpan` untuk rak pribadi. Riwayat tercatat otomatis.
3. **Guru** (`/login-guru`): sama seperti siswa + halaman `/videos`.

## Import siswa (CSV)

Format: `nis,name,class` per baris (maks 2 MB). Header `nis,name,class`
opsional. NIS yang sama akan diperbarui.

```csv
nis,name,class
18038,Ahmad Dani,XII IPA 2
18039,Rina Wulandari,X TKJ 1
```

Via: **Kelola → Data Pengguna → Import CSV**.

## Konfigurasi chatbot

```env
CHATBOT_PROVIDER=local   # local | gemini | openai
GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.0-flash
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini
```

`local` = murni dari teks PDF + judul/deskripsi (tanpa biaya/API).
Provider lain gagal → otomatis fallback ke lokal.

## Perintah berguna

```bash
php artisan test                 # uji otomatis
php artisan migrate --seed       # aman diulang (upsert)
php artisan storage:link         # wajib agar cover/PDF tampil
npm run dev                      # front-end saat pengembangan
```

## Catatan teknis

- Cover di disk `public` (`/storage/...`), PDF di disk private
  (`storage/app/private`) dan hanya dilayani lewat `GET /ebooks/{id}/read`
  ber-auth. File lama di `public/ebooks/files` tetap didukung sebagai
  fallback baca/ekstrak sampai di-upload ulang, tapi upload baru selalu
  private. Jangan unggah materi berhak cipta terbatas.
- Login siswa hanya NIS (tanpa password) sesuai kebutuhan sekolah;
  rate-limit aktif terhadap brute-force/tebak NIS.
- Statistik dashboard di-cache 60 detik.
- Pencarian memakai `LIKE ... ESCAPE '\'` eksplisit (aman untuk `%_\` di
  MySQL, SQLite, PgSQL).
- Registrasi publik **dimatikan**; akun dibuat admin. Reset password /
  verifikasi email bawaan Breeze tidak dipakai (semua user `email=null`).
