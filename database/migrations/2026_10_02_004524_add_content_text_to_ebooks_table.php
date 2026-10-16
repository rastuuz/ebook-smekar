<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Teks hasil ekstraksi PDF untuk chatbot (retrieval lokal).
     * Nullable: e-book tanpa PDF atau PDF scan tidak punya teks.
     */
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->longText('content_text')->nullable()->after('file');
            $table->timestamp('content_indexed_at')->nullable()->after('content_text');
        });
    }

    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn(['content_text', 'content_indexed_at']);
        });
    }
};
