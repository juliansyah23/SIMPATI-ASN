<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom `deleted_at` (soft delete) ke tabel:
 * - users          → hapus user tanpa hilangkan riwayat pengisian
 * - questionnaires → hapus kuisioner tanpa hilangkan data survei
 *
 * Tabel turunan (categories, questions, demografi_fields, survey_responses, dst.)
 * tidak perlu soft delete karena diakses lewat relasi kuisioner/user yang aktif;
 * jika kuisioner di-soft-delete, data turunannya otomatis tidak muncul di query
 * yang menggunakan scope Eloquent (withTrashed, dll.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('questionnaires', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};