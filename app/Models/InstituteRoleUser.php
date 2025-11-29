<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InstituteRoleUser extends Pivot
{
    protected $fillable = [ 'institute_id', 'user_id', 'role_id', 'assigned_by',];
}
