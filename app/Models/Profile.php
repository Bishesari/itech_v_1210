<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = ['user_id', 'identifier_type', 'n_code', 'f_name_fa', 'l_name_fa'];
    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
