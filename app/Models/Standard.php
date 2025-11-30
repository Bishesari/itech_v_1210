<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Standard extends Model
{
    protected $fillable = ['field_id', 'code', 'name_fa', 'name_en', 'abb', 'nazari_h', 'amali_h', 'karvarzi_h', 'project_h', 'sum_h'];
    public function field():BelongsTo{
        return $this->belongsTo(Field::class);
    }

    public function chapters():HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('number');
    }

    public function questions():HasManyThrough
    {
        return $this->hasManyThrough(Question::class, Chapter::class);
    }
}
