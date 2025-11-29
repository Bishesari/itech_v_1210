<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    protected $fillable= ['mobile_nu', 'verified'];
    public function users():belongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
