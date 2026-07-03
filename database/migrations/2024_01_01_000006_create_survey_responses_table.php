<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu baris = satu kali pengisian kuisioner oleh satu user.
     * Status "draft" dipakai selama proses pengisian per-kategori berlangsung
     * (mengganti session yang dipakai KuisionerController saat ini),
     * sehingga progres tidak hilang walau browser ditutup.
     */
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('questionnaire_id')->constrained('questionnaires')->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->unsignedInteger('current_step')->default(1);
            $table->decimal('rata_rata', 4, 2)->nullable(); // dihitung saat submit
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // satu user hanya boleh punya satu pengisian (draft/submitted) per kuisioner
            $table->unique(['user_id', 'questionnaire_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
