<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamUser extends Pivot
{
    public function answers():HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_user_id');
    }
    public function exam():BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    protected $casts = [
        'started_at' => 'datetime',
    ];
}
