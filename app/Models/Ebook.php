<?php

namespace App\Models;

use App\Support\Like;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Ebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'created_by',
        'title',
        'author',
        'description',
        'cover',
        'file',
        'content_text',
        'content_indexed_at',
    ];

    protected $casts = [
        'content_indexed_at' => 'datetime',
    ];

    public function hasIndexedContent(): bool
    {
        return ! blank($this->content_text);
    }

    /**
     * Status indeks chatbot: no-file | ready | needs-attention.
     * - no-file: belum ada PDF.
     * - ready: teks berhasil diekstrak, chatbot bisa mengutip isi.
     * - needs-attention: ada PDF tapi teks tak terbaca (scan/gambar/gagal).
     */
    public function indexStatus(): string
    {
        if (blank($this->file)) {
            return 'no-file';
        }

        return $this->hasIndexedContent() ? 'ready' : 'needs-attention';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Kontributor: guru/admin yang menambahkan e-book ini.
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(EbookVideo::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function coverUrl(): Attribute
    {
        return Attribute::get(fn () => $this->cover
            ? Storage::disk('public')->url($this->cover)
            : null);
    }

    /**
     * URL baca PDF selalu lewat controller ber-auth (bukan URL /storage langsung),
     * agar file private tidak bisa ditebak/diakses tanpa login.
     */
    public function fileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->file
            ? route('ebooks.read', $this)
            : null);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (blank($keyword)) {
            return $query;
        }

        $keyword = trim(mb_substr($keyword, 0, 100));

        // ESCAPE eksplisit agar % _ \ diperlakukan sebagai teks biasa di semua driver DB.
        return $query->where(function ($q) use ($keyword) {
            Like::whereLike($q, 'title', $keyword);
            Like::orWhereLike($q, 'author', $keyword);
            $q->orWhereHas('category', function ($cq) use ($keyword) {
                Like::whereLike($cq, 'name', $keyword);
            });
        });
    }
}
