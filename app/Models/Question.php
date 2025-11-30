<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['chapter_id', 'text', 'difficulty', 'is_final', 'assigned_by'];
    public function chapter():BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
    public function maker():BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    public function options():HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function exams():BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_question')->withTimestamps();
    }

    public const CLUSTERS = [
        'easy' => 'ساده',
        'medium' => 'متوسط',
        'hard' => 'سخت',
    ];
}
