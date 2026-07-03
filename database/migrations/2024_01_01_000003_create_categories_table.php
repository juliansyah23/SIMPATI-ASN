<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kategori/dimensi dalam satu kuisioner.
     * Kategori pertama biasanya tipe "demografi", sisanya "likert" (dimensi psikososial).
     * Dibuat per-questionnaire (bukan global) supaya tiap kuisioner bisa punya struktur
     * dimensi yang berbeda di masa depan, tapi untuk data awal akan disamakan dengan
     * struktur 8 kategori yang sudah ada di KuisionerController.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->string('kode');             // contoh: persepsi_kebijakan, motivasi_kerja, dst.
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->enum('type', ['demografi', 'likert']);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->unique(['questionnaire_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
