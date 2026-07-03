<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyDemografi extends Model
{
    use HasFactory;

    protected $table = 'survey_demografi';

    protected $fillable = [
        'survey_response_id',
        'demografi_field_id',
        'value',
    ];

    public function surveyResponse()
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function field()
    {
        return $this->belongsTo(DemografiField::class, 'demografi_field_id');
    }
}
