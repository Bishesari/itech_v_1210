<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $fillable = ['exam_user_id', 'question_id', 'option_id', 'is_correct'];

    public function examUser():BelongsTo
    {
        return $this->belongsTo(ExamUser::class);
    }

    public function question():BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option():BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
