<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    protected $fillable = ['title'];
    public function standards():HasMany
    {
        return $this->hasMany(Standard::class);
    }
}
