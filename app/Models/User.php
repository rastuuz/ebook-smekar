<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_GURU = 'guru';

    public const ROLE_SISWA = 'siswa';

    protected $fillable = [
        'nis',
        'username',
        'name',
        'class',
        'role',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }

    /**
     * Staf = admin & guru. Boleh masuk portal staf (login username + akses baca).
     * Hak KELOLA (create/update/delete kategori, e-book, video, pengguna)
     * khusus admin — lihat middleware EnsureAdmin & FormRequest authorize().
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_GURU], true);
    }

    /**
     * Alias lama agar kode pemanggil tidak rusak.
     * Jangan dipakai untuk cek hak kelola — hak kelola khusus admin.
     */
    public function canManageContent(): bool
    {
        return $this->isStaff();
    }

    public function canAccessStaffArea(): bool
    {
        return $this->isStaff();
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function savedEbooks(): BelongsToMany
    {
        return $this->belongsToMany(Ebook::class, 'bookmarks')->withTimestamps();
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function hasBookmarked(Ebook $ebook): bool
    {
        return $this->bookmarks()->where('ebook_id', $ebook->id)->exists();
    }
}
