<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\Like;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $role = request('role', User::ROLE_SISWA);
        if (! in_array($role, [User::ROLE_SISWA, User::ROLE_GURU], true)) {
            $role = User::ROLE_SISWA;
        }

        $search = trim(mb_substr((string) request('q', ''), 0, 50));

        $query = User::query()->where('role', $role);

        if ($search !== '') {
            // Siswa dicari di nama/NIS/kelas; guru di nama/NIP (username).
            $query->where(function ($q) use ($search, $role) {
                Like::whereLike($q, 'name', $search);
                if ($role === User::ROLE_SISWA) {
                    Like::orWhereLike($q, 'nis', $search);
                    Like::orWhereLike($q, 'class', $search);
                } else {
                    Like::orWhereLike($q, 'username', $search);
                }
            });
        }

        // Siswa diurut NIS agar gampang cek kelengkapan 18022-19747,
        // guru/staf tetap urut nama.
        if ($role === User::ROLE_SISWA) {
            $users = $query->orderBy('nis')->paginate(50)->withQueryString();
        } else {
            $users = $query->orderBy('name')->paginate(50)->withQueryString();
        }

        $counts = [
            User::ROLE_SISWA => User::where('role', User::ROLE_SISWA)->count(),
            User::ROLE_GURU => User::where('role', User::ROLE_GURU)->count(),
        ];

        return view('admin.users.index', compact('users', 'role', 'counts', 'search'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['role'] === User::ROLE_GURU) {
            // Guru login via username: kredensial siswa dibersihkan agar tak ada data basi.
            $data['nis'] = null;
            $data['class'] = null;
        } else {
            // Siswa login via NIS: kredensial staf dibersihkan.
            $data['username'] = null;
            $data['password'] = null;
        }

        $data['email'] = $data['email'] ?? null;

        User::create($data);

        Cache::forget('stats:admin-dashboard');

        $label = $data['role'] === User::ROLE_GURU ? 'Guru' : 'Siswa';

        return redirect()->route('admin.users.index', ['role' => $data['role']])
            ->with('success', "Data {$label} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        if ($user->isAdmin()) {
            abort(403, 'Akun admin tidak dapat diubah dari sini.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            abort(403, 'Akun admin tidak dapat diubah dari sini.');
        }

        $data = $request->validated();

        if ($data['role'] === User::ROLE_GURU) {
            // Guru login via username: kredensial siswa dibersihkan agar tak ada data basi.
            $data['nis'] = null;
            $data['class'] = null;

            if (empty($data['password'])) {
                unset($data['password']);
            }
            // Jika terisi, biarkan plain — cast 'hashed' meng-hash otomatis.
        } else {
            // Siswa login via NIS: kredensial staf dibersihkan.
            $data['username'] = null;
            $data['password'] = null;
        }

        $data['email'] = $data['email'] ?? null;

        $user->update($data);

        Cache::forget('stats:admin-dashboard');

        $label = $data['role'] === User::ROLE_GURU ? 'Guru' : 'Siswa';

        return redirect()->route('admin.users.index', ['role' => $data['role']])
            ->with('success', "Data {$label} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Akun admin tidak dapat dihapus dari sini.');
        }

        $label = $user->isGuru() ? 'Guru' : 'Siswa';
        $role = $user->isGuru() ? User::ROLE_GURU : User::ROLE_SISWA;
        $user->delete();

        Cache::forget('stats:admin-dashboard');

        return redirect()->route('admin.users.index', ['role' => $role])
            ->with('success', "Data {$label} berhasil dihapus.");
    }

    /**
     * Form import pengguna dari CSV/Excel: siswa (nis,name,class)
     * atau guru (username,name) + sandi default sama.
     */
    public function importForm(): View
    {
        return view('admin.users.import');
    }

    /**
     * Import massal dari CSV atau Excel (.xlsx/.xls, sheet pertama).
     * - Siswa: nis,name,class → upsert berdasar NIS.
     * - Guru: username,name → upsert berdasar username, semua memakai
     *   sandi default sama agar guru bisa login lalu ganti sendiri
     *   via Profil. Password lama TIDAK ditimpa kecuali centang reset
     *   atau akun belum punya password.
     * Header baris pertama opsional & urutan bebas (nis/nama/kelas,
     * username/nama/password, toleran varian tulisan).
     */
    public function import(): RedirectResponse
    {
        // Import massal (puluhan-ratusan guru) butuh waktu hashing bcrypt
        // per akun — naikkan batas agar tidak 500 max execution time.
        @set_time_limit(0);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $validated = request()->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
            'role' => ['sometimes', Rule::in([User::ROLE_SISWA, User::ROLE_GURU])],
            'default_password' => ['required_if:role,guru', 'nullable', 'string', 'min:8', 'max:255'],
            'reset_password' => ['sometimes', 'boolean'],
        ], [
            'file.required' => 'Pilih file CSV/Excel dulu.',
            'file.mimes' => 'File harus CSV atau Excel (.csv, .xlsx, .xls).',
            'file.max' => 'Ukuran file maksimal 5 MB.',
            'default_password.required_if' => 'Sandi default wajib diisi untuk import guru (min 8 karakter).',
            'default_password.min' => 'Sandi default minimal 8 karakter.',
        ]);

        // Default siswa agar request lama (hanya file) tetap jalan.
        $role = $validated['role'] ?? User::ROLE_SISWA;

        try {
            $rows = $this->readImportRows($validated['file']);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'File tidak bisa dibaca. Pastikan format CSV/XLSX valid.');
        }

        try {
            DB::beginTransaction();

            if ($role === User::ROLE_GURU) {
                [$imported, $skipped] = $this->importGuruRows($rows, $validated['default_password'] ?? '', request()->boolean('reset_password'));
            } else {
                [$imported, $skipped] = $this->importSiswaRows($rows);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        Cache::forget('stats:admin-dashboard');

        $label = $role === User::ROLE_GURU ? 'guru' : 'siswa';

        return redirect()->route('admin.users.index', ['role' => $role])
            ->with('success', "Import selesai: {$imported} {$label} tersimpan, {$skipped} baris dilewati.");
    }

    /**
     * Baca file upload jadi array baris (string semua).
     * Dukung .csv/.txt (auto delimiter , atau ;) dan .xlsx/.xls (sheet pertama).
     *
     * @return array<int, array<int, string>>
     */
    private function readImportRows($file): array
    {
        $ext = mb_strtolower($file->getClientOriginalExtension() ?: '');
        if ($ext === '') {
            $ext = mb_strtolower(pathinfo((string) $file->getClientOriginalName(), PATHINFO_EXTENSION));
        }

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());

            // Baca SEMUA sheet (file sekolah sering 1 sheet per kelas:
            // X-1, XI-2, XII-...). Dulu hanya sheet pertama yang kebaca
            // sehingga siswa di sheet lain hilang.
            $rows = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                // formatData=true agar tanggal/angka tampil seperti di Excel.
                $raw = $sheet->toArray(null, true, true, false);

                foreach ($raw as $rawRow) {
                    $row = [];
                    foreach ((array) $rawRow as $cell) {
                        $row[] = self::stringifyCell($cell);
                    }
                    // Lewati baris kosong agar header sheet berikut
                    // tidak tercampur dengan data.
                    if (self::isEmptyRow($row)) {
                        continue;
                    }
                    $rows[] = $row;
                    if (count($rows) > 5000) {
                        break 2;
                    }
                }
            }

            return $rows;
        }

        // CSV/TXT: deteksi delimiter koma vs titik-koma (Excel Indonesia sering pakai ;).
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('File tidak bisa dibuka.');
        }

        try {
            $firstLines = [];
            for ($i = 0; $i < 2; $i++) {
                $pos = ftell($handle);
                $line = fgets($handle);
                if ($line === false) {
                    break;
                }
                $firstLines[] = $line;
                fseek($handle, $pos);
                // Kembalikan ke awal baris agar fgetcsv baca utuh.
                break;
            }
            $sample = implode("\n", $firstLines);
            $commas = substr_count($sample, ',');
            $semis = substr_count($sample, ';');
            $tabs = substr_count($sample, "\t");
            $delimiter = ',';
            if ($semis > $commas && $semis >= $tabs) {
                $delimiter = ';';
            } elseif ($tabs > $commas && $tabs > $semis) {
                $delimiter = "\t";
            }

            $rows = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rows[] = array_map(fn ($c) => self::stringifyCell($c), (array) $row);
                if (count($rows) > 5000) {
                    break;
                }
            }

            return $rows;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    private static function stringifyCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '';
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            // Hindari 18038.0 → "18038", tapi 12.5 tetap "12.5".
            if (floor($value) == $value && abs($value) < 1e15) {
                return (string) (int) $value;
            }

            return rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');
        }
        $s = trim((string) $value);
        // Bersihkan BOM di awal sel.
        if (str_starts_with($s, "\xEF\xBB\xBF")) {
            $s = substr($s, 3);
        }

        return trim($s);
    }

    /**
     * Petakan header ke indeks kolom. Kembalikan null jika baris bukan header.
     *
     * @param array<int,string> $row
     * @param array<string,array<int,string>> $synonyms field => daftar alias
     * @return array<string,int>|null
     */
    private static function detectHeaderMap(array $row, array $synonyms): ?array
    {
        $norm = array_map(fn ($c) => mb_strtolower(trim((string) preg_replace('/\s+/', ' ', $c))), $row);
        $map = [];
        foreach ($synonyms as $field => $aliases) {
            foreach ($aliases as $alias) {
                $idx = array_search($alias, $norm, true);
                if ($idx !== false) {
                    $map[$field] = $idx;
                    break;
                }
            }
        }
        // Anggap header jika minimal 2 kolom kunci ketemu (cukup toleran untuk Excel user).
        $hits = count($map);

        return $hits >= 2 ? $map : null;
    }

    private static function isEmptyRow(array $row): bool
    {
        foreach ($row as $c) {
            if (trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array<int,string>> $rows
     * @return array{0:int,1:int} [imported, skipped]
     */
    private function importSiswaRows(array $rows): array
    {
        $imported = 0;
        $skipped = 0;
        $rowsRead = 0;
        $maxRows = 5000;
        $seenNis = [];
        $map = null;
        $firstChecked = false;
        $synonyms = [
            'nis' => ['nis', 'nisn', 'no induk', 'nomor induk', 'no. induk', 'no_induk', 'induk'],
            'name' => ['nama', 'name', 'nama lengkap', 'nama siswa', 'nama_lengkap', 'nama_siswa'],
            'class' => ['kelas', 'class', 'rombel', 'tingkat', 'kelas/rombel', 'kelas rombel', 'kelas / rombel'],
        ];

        foreach ($rows as $row) {
            $rowsRead++;
            if ($rowsRead > $maxRows) {
                $skipped++;

                break;
            }
            if (self::isEmptyRow($row)) {
                continue;
            }

            // Lewati baris header di sheet mana pun (dukung multi-sheet
            // di mana tiap sheet punya header sendiri).
            if (self::detectHeaderMap($row, $synonyms) !== null) {
                if (! $firstChecked) {
                    $firstChecked = true;
                    $map = self::detectHeaderMap($row, $synonyms);
                }

                continue;
            }

            // Header opsional & urutan bebas: nis/nama (kelas opsional).
            if (! $firstChecked) {
                $firstChecked = true;
                $map = self::detectHeaderMap($row, $synonyms);
                if ($map !== null) {
                    continue;
                }
                // Fallback header persis lama: nis,name,class.
                $cells = array_map(fn ($c) => mb_strtolower(trim((string) $c)), $row);
                if (in_array('nis', $cells, true)) {
                    continue;
                }
            }

            if ($map !== null) {
                // Pakai indeks dari header — jangan fallback posisi untuk
                // kelas agar file 2 kolom (NIS, Nama) tidak geser.
                $nis = isset($map['nis']) ? trim((string) ($row[$map['nis']] ?? '')) : '';
                $name = isset($map['name']) ? trim((string) ($row[$map['name']] ?? '')) : '';
                $class = isset($map['class']) ? trim((string) ($row[$map['class']] ?? '')) : '';
                // Jika header tak lengkap untuk NIS/nama, coba posisi umum.
                if ($nis === '' && count($row) >= 1) {
                    $nis = trim((string) ($row[0] ?? ''));
                }
                if ($name === '' && count($row) >= 2) {
                    $name = trim((string) ($row[1] ?? ''));
                }
            } else {
                // Tanpa header: dukung [NIS, Nama], [NIS, Nama, Kelas],
                // dan [No, NIS, Nama(, Kelas)] yang umum di file sekolah.
                $cells = array_values(array_map(fn ($c) => trim((string) $c), $row));
                $nis = '';
                $name = '';
                $class = '';
                if (count($cells) === 2) {
                    [$nis, $name] = $cells;
                } elseif (count($cells) >= 3) {
                    // Jika kolom pertama mirip nomor urut (1-3 digit) dan
                    // kolom kedua mirip NIS (>=4 karakter), anggap format
                    // [No, NIS, Nama(, Kelas)].
                    $c0 = $cells[0];
                    $c1 = $cells[1];
                    if (preg_match('/^\d{1,3}$/', $c0) && mb_strlen($c1) >= 4) {
                        $nis = $c1;
                        $name = $cells[2] ?? '';
                        $class = trim((string) ($cells[3] ?? ''));
                    } else {
                        $nis = $c0;
                        $name = $c1;
                        $class = trim((string) ($cells[2] ?? ''));
                    }
                } else {
                    $nis = trim((string) ($cells[0] ?? ''));
                }
            }

            // Bersihkan marker kutip Excel ('nama) yang sering muncul
            // saat kolom diformat Text, mis. 'INDAH.
            $nis = ltrim($nis, "'\"\xE2\x80\x98\xE2\x80\x99");
            $name = ltrim($name, "'\"\xE2\x80\x98\xE2\x80\x99");
            $class = ltrim($class, "'\"\xE2\x80\x98\xE2\x80\x99");
            $nis = trim($nis);
            $name = trim(preg_replace('/\s+/', ' ', $name));
            $class = trim($class);

            // Koreksi otomatis jika kolom tertukar (NIS berisi nama,
            // Nama berisi NIS): mis. NIS="ZAKLY DWI SAPUTRA",
            // Nama="16375" -> tukar.
            if ($nis !== '' && $name !== '' && str_contains($nis, ' ') && preg_match('/^[A-Za-z0-9_.-]{4,20}$/', $name)) {
                [$nis, $name] = [$name, $nis];
            }

            // Kelas opsional: file cukup NIS + Nama.
            $class = $class !== '' ? $class : null;
            // Abaikan kelas sampah dari baris rekap (P/L/:).
            if ($class !== null && (mb_strlen($class) < 2 || mb_strlen($class) > 50)) {
                $class = mb_strlen($class) > 50 ? $class : null;
            }

            // Filter baris rekap/footer (JML, L/P, angka saja) agar tidak
            // jadi akun siswa palsu seperti ": 14 | P".
            $nameLower = mb_strtolower($name);
            $nisLower = mb_strtolower($nis);
            $junkNames = ['jml', 'jumlah', 'total', 'l', 'p', 'lk', 'pr', 'laki', 'perempuan', 'laki-laki', 'keterangan', 'no', 'nama', 'nis', 'kelas'];
            $valid = $nis !== '' && $name !== ''
                && preg_match('/^[A-Za-z0-9_.-]{4,20}$/', $nis)
                && ! in_array($nisLower, ['nis', 'nisn', 'nama', 'kelas', 'no'], true)
                && mb_strlen($name) >= 3 && mb_strlen($name) <= 255
                && preg_match('/[A-Za-z]/', $name)
                && ! preg_match('/^\d+$/', $name)
                && ! in_array($nameLower, $junkNames, true);

            if (! $valid) {
                $skipped++;

                continue;
            }

            // Lewati duplikat NIS dalam file yang sama (pakai baris pertama).
            if (isset($seenNis[$nis])) {
                $skipped++;

                continue;
            }
            $seenNis[$nis] = true;

            // Lindungi akun staf: NIS milik admin/guru jangan dioverwrite jadi siswa.
            $staff = User::where('nis', $nis)
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_GURU])
                ->exists();
            if ($staff) {
                $skipped++;

                continue;
            }

            User::updateOrCreate(
                ['nis' => $nis],
                [
                    'name' => $name,
                    'class' => $class,
                    'role' => User::ROLE_SISWA,
                    'username' => null,
                    'password' => null,
                    'email' => null,
                ]
            );
            $imported++;
        }

        return [$imported, $skipped];
    }

    /**
     * Import guru: username,name[,password] — urutan bebas jika ada header.
     * Kolom password opsional — jika kosong dipakai sandi default
     * yang sama untuk semua. Password hanya ditulis untuk akun BARU,
     * atau jika reset diminta / akun belum punya password, sehingga
     * guru yang sudah ganti sandi sendiri tidak ke-reset diam-diam.
     *
     * @param array<int, array<int,string>> $rows
     * @return array{0:int,1:int} [imported, skipped]
     */
    private function importGuruRows(array $rows, string $defaultPassword, bool $reset): array
    {
        $imported = 0;
        $skipped = 0;
        $rowsRead = 0;
        $maxRows = 5000;
        $seenUsernames = [];
        $map = null;
        $firstChecked = false;
        $guruSynonyms = [
            'username' => ['username', 'user', 'user name', 'nip', 'nik', 'nuptk', 'id', 'kode', 'kode guru', 'user_name'],
            'name' => ['nama', 'name', 'nama lengkap', 'nama guru', 'nama_lengkap', 'nama_guru'],
            'password' => ['password', 'sandi', 'pass', 'kata sandi', 'kata_sandi', 'password default'],
        ];

        foreach ($rows as $row) {
            $rowsRead++;
            if ($rowsRead > $maxRows) {
                $skipped++;

                break;
            }
            if (self::isEmptyRow($row)) {
                continue;
            }

            // Lewati baris header di sheet mana pun (dukung multi-sheet).
            if (self::detectHeaderMap($row, $guruSynonyms) !== null) {
                if (! $firstChecked) {
                    $firstChecked = true;
                    $map = self::detectHeaderMap($row, $guruSynonyms);
                }

                continue;
            }

            // Header opsional & urutan bebas: username/nama/password (+varian NIP).
            if (! $firstChecked) {
                $firstChecked = true;
                $map = self::detectHeaderMap($row, $guruSynonyms);
                if ($map !== null) {
                    continue;
                }
                $cells = array_map(fn ($c) => mb_strtolower(trim((string) $c)), $row);
                if (in_array('username', $cells, true)) {
                    continue;
                }
            }

            if ($map !== null) {
                $username = trim((string) ($row[$map['username']] ?? ''));
                $name = trim((string) ($row[$map['name']] ?? ''));
                $rowPassword = isset($map['password']) ? trim((string) ($row[$map['password']] ?? '')) : '';
                if (($username === '' || $name === '') && count($row) >= 2) {
                    $username = $username !== '' ? $username : trim((string) ($row[0] ?? ''));
                    $name = $name !== '' ? $name : trim((string) ($row[1] ?? ''));
                    if ($rowPassword === '') {
                        $rowPassword = trim((string) ($row[2] ?? ''));
                    }
                }
                // Excel guru kadang hanya NIP + Password tanpa kolom Nama:
                // pakai NIP sebagai nama agar baris tidak terbuang.
                if ($username !== '' && $name === '') {
                    $name = $username;
                }
            } else {
                // Tanpa header: dukung [Username, Nama(, Password)] dan
                // [No, Username/NIP, Nama] yang umum di file sekolah.
                $cells = array_values(array_map(fn ($c) => trim((string) $c), $row));
                $c1nospace = preg_replace('/\s+/', '', (string) ($cells[1] ?? ''));
                if (count($cells) >= 3 && preg_match('/^\d{1,3}$/', $cells[0] ?? '') && preg_match('/^[A-Za-z0-9_.-]{2,50}$/', $c1nospace)) {
                    $username = $cells[1];
                    $name = $cells[2] ?? '';
                    $rowPassword = trim((string) ($cells[3] ?? ''));
                } else {
                    $username = trim((string) ($cells[0] ?? ''));
                    $name = trim((string) ($cells[1] ?? ''));
                    $rowPassword = trim((string) ($cells[2] ?? ''));
                }
                // Tanpa header & tanpa nama: pakai username sebagai nama.
                if ($username !== '' && $name === '') {
                    $name = $username;
                }
            }

            $username = ltrim(trim((string) $username), "'\"\xE2\x80\x98\xE2\x80\x99");
            $name = trim(preg_replace('/\s+/', ' ', ltrim(trim((string) $name), "'\"\xE2\x80\x98\xE2\x80\x99")));
            // NIP sering ditulis pakai spasi (19850312 200901 1 001):
            // buang semua spasi agar bisa dipakai sebagai username login.
            // Guru login tanpa spasi, mis. 198503122009011001.
            $username = preg_replace('/\s+/', '', $username);

            if ($username === '' || $name === '' || mb_strlen($username) > 50 || mb_strlen($name) > 255) {
                $skipped++;

                continue;
            }

            // Username hanya huruf/angka/titik/strip/underscore agar aman untuk login.
            if (! preg_match('/^[A-Za-z0-9_.-]+$/', $username)) {
                $skipped++;

                continue;
            }

            $passwordToUse = $rowPassword !== '' ? $rowPassword : $defaultPassword;
            if (mb_strlen($passwordToUse) < 8 || mb_strlen($passwordToUse) > 255) {
                $skipped++;

                continue;
            }

            $key = mb_strtolower($username);
            if (isset($seenUsernames[$key])) {
                $skipped++;

                continue;
            }
            $seenUsernames[$key] = true;

            $existing = User::where('username', $username)->first();

            // Lindungi akun admin: username milik admin jangan dioverwrite jadi guru.
            if ($existing && $existing->isAdmin()) {
                $skipped++;

                continue;
            }

            if ($existing) {
                $payload = [
                    'name' => $name,
                    'role' => User::ROLE_GURU,
                    'nis' => null,
                    'class' => null,
                ];
                // Tulis password hanya jika reset dicentang atau akun belum punya password.
                // Cast 'hashed' meng-hash otomatis dari plain.
                if ($reset || empty($existing->password)) {
                    $payload['password'] = $passwordToUse;
                }
                $existing->update($payload);
                $imported++;

                continue;
            }

            User::create([
                'username' => $username,
                'name' => $name,
                'role' => User::ROLE_GURU,
                'nis' => null,
                'class' => null,
                'email' => null,
                'password' => $passwordToUse,
            ]);
            $imported++;
        }

        return [$imported, $skipped];
    }
}
