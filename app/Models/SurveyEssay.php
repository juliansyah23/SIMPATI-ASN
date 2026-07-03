<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyEssay extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_response_id',
        'question_id',
        'jawaban',
    ];

    public function surveyResponse()
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
