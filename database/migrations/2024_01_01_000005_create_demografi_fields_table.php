<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Field-field pada kategori tipe "demografi" (mis. Pusat Riset, Posisi,
     * Mode Kerja, Pola Kehadiran). Tipe "choice" menyimpan pilihan di kolom `options` (JSON).
     */
    public function up(): void
    {
        Schema::create('demografi_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('field_key');   // contoh: pola_kehadiran
            $table->string('label');
            $table->enum('type', ['choice', 'text'])->default('choice');
            $table->json('options')->nullable(); // array pilihan untuk type=choice
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->unique(['category_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demografi_fields');
    }
};
