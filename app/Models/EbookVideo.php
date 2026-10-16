<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'created_by',
        'title',
        'youtube_url',
        'description',
    ];

    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    /**
     * Kontributor: guru/admin yang menambahkan karya ini.
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function youtubeId(): Attribute
    {
        return Attribute::get(fn () => self::extractYoutubeId($this->youtube_url));
    }

    public function embedUrl(): Attribute
    {
        return Attribute::get(function () {
            $id = self::extractYoutubeId($this->youtube_url);

            return $id ? "https://www.youtube-nocookie.com/embed/{$id}" : null;
        });
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $patterns = [
            '/youtu\.be\/([A-Za-z0-9_-]{11})/',
            '/v=([A-Za-z0-9_-]{11})/',
            '/embed\/([A-Za-z0-9_-]{11})/',
            '/shorts\/([A-Za-z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
