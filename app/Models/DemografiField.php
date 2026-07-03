<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemografiField extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'field_key',
        'label',
        'type',
        'options',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyDemografi::class);
    }
}
