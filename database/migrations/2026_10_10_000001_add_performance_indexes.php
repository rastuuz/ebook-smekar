<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index ringan untuk filter/search yang sering dipakai:
     * users.role (filter admin), users.class (rekap per kelas),
     * ebooks.title/author (LIKE search), ebooks.content_indexed_at
     * (query "belum diindeks" di dashboard admin).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('class');
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->index('title');
            $table->index('author');
            $table->index('content_indexed_at');
        });

        Schema::table('reading_histories', function (Blueprint $table) {
            $table->index('last_read_at');
        });
    }

    public function down(): void
    {
        Schema::table('reading_histories', function (Blueprint $table) {
            $table->dropIndex(['last_read_at']);
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['author']);
            $table->dropIndex(['content_indexed_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['class']);
        });
    }
};
