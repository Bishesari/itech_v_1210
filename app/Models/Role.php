<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name_fa', 'name_en', 'assignable_by_founder', 'created', 'updated'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institute_role_user')
            ->withPivot(['institute_id', 'assigned_by'])
            ->withTimestamps();
    }
    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class, 'institute_role_user')
            ->withPivot(['user_id', 'assigned_by'])
            ->withTimestamps();
    }
}
