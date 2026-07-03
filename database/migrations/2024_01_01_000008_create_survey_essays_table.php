<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jawaban esai (teks terbuka) per pertanyaan per pengisian.
     */
    public function up(): void
    {
        Schema::create('survey_essays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->timestamps();

            $table->unique(['survey_response_id', 'question_id'], 'survey_essays_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_essays');
    }
};
