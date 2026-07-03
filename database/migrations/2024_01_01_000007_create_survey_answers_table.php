<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jawaban skala Likert (1-5) per pertanyaan per pengisian.
     * Disimpan ternormalisasi (bukan JSON) agar mudah dipakai untuk
     * agregasi/analisis psikososial per dimensi di Tahap 5.
     */
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor'); // 1-5
            $table->timestamps();

            $table->unique(['survey_response_id', 'question_id'], 'survey_answers_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
