<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jawaban field demografi (mis. pola_kehadiran = "WFO 3x/minggu") per pengisian.
     */
    public function up(): void
    {
        Schema::create('survey_demografi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->cascadeOnDelete();
            $table->foreignId('demografi_field_id')->constrained('demografi_fields')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['survey_response_id', 'demografi_field_id'], 'survey_demografi_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_demografi');
    }
};
