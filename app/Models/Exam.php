<?php

namespace App\Models;

use App\Traits\HasJalaliDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exam extends Model
{
    use HasJalaliDates;
    protected $fillable = ['standard_id', 'title', 'question_count', 'exam_time', 'start_date', 'end_date'];

    public function questions():BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_question')->withTimestamps();
    }

    public function standard():BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'exam_user')
            ->withPivot(['started_at', 'finished_at', 'score', 'is_finished', 'question_order'])
            ->withTimestamps();
    }


}
