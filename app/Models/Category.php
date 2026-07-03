<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'kode',
        'title',
        'subtitle',
        'type',
        'urutan',
    ];

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /** Pertanyaan likert (skala 1-5) pada kategori ini. */
    public function likertQuestions()
    {
        return $this->hasMany(Question::class)->where('type', 'likert')->orderBy('urutan');
    }

    /** Pertanyaan esai (teks terbuka) pada kategori ini. */
    public function esaiQuestions()
    {
        return $this->hasMany(Question::class)->where('type', 'esai')->orderBy('urutan');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('urutan');
    }

    /** Field demografi (hanya relevan jika type = demografi). */
    public function demografiFields()
    {
        return $this->hasMany(DemografiField::class)->orderBy('urutan');
    }
}
