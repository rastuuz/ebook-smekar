<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda kontribusi: siapa (guru/admin) yang menambahkan
     * e-book / video. Nullable agar data lama tetap valid,
     * nullOnDelete agar karya tidak ikut hilang saat akun dihapus.
     */
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('ebook_videos', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('ebook_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ebook_videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
