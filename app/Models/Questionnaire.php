<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'judul',
        'tahun',
        'deskripsi',
        'status',
        'created_by',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class)->orderBy('urutan');
    }

    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    /** Apakah user tertentu sudah pernah submit kuisioner ini. */
    public function sudahDiisiOleh(int $userId): bool
    {
        return $this->surveyResponses()
            ->where('user_id', $userId)
            ->where('status', 'submitted')
            ->exists();
    }
}