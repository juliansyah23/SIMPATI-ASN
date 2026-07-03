<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'questionnaire_id',
        'status',
        'current_step',
        'rata_rata',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'rata_rata' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    public function essays()
    {
        return $this->hasMany(SurveyEssay::class);
    }

    public function demografi()
    {
        return $this->hasMany(SurveyDemografi::class);
    }

    /** Hitung & simpan rata-rata skor likert dari semua jawaban. */
    public function hitungRataRata(): float
    {
        $rata = round((float) $this->answers()->avg('skor'), 2);
        $this->update(['rata_rata' => $rata]);

        return $rata;
    }
}
