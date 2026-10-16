<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Helper pencarian LIKE yang aman lintas database (MySQL/SQLite/PgSQL).
 *
 * Memakai '!' sebagai karakter ESCAPE (bukan backslash) karena tiap
 * driver menafsirkan backslash secara berbeda:
 * - MySQL: '\\\\' = 1 backslash, '\' = error 1064.
 * - SQLite: '\\\\' = 2 karakter, ditolak ("must be a single character").
 * Dengan ESCAPE '!' tidak ada ambiguitas di semua driver.
 */
class Like
{
    /**
     * Escape karakter khusus LIKE memakai '!': ! % _
     * (backslash diperlakukan sebagai karakter biasa).
     */
    public static function escape(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    /**
     * Tambahkan kondisi "kolom LIKE %keyword%" dengan ESCAPE eksplisit.
     */
    public static function whereLike(Builder $query, string $column, string $keyword, string $boolean = 'and'): Builder
    {
        return $query->whereRaw($column." LIKE ? ESCAPE '!'", ['%'.self::escape($keyword).'%'], $boolean);
    }

    /**
     * Varian orWhere untuk LIKE dengan ESCAPE eksplisit.
     */
    public static function orWhereLike(Builder $query, string $column, string $keyword): Builder
    {
        return self::whereLike($query, $column, $keyword, 'or');
    }
}
