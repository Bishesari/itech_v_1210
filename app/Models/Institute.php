<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Institute extends Model
{
    protected $fillable = ['short_name', 'full_name', 'abb', 'remain_credit'];


    // تمام کاربران مرتبط با مؤسسه (با همه نقش‌ها)
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'institute_role_user')
            ->withPivot(['role_id', 'assigned_by'])
            ->withTimestamps();
    }

    // تمام نقش‌های مرتبط با مؤسسه
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'institute_role_user')
            ->withPivot(['user_id', 'assigned_by'])
            ->withTimestamps();
    }

    // فقط موسسان (کاربرانی با نقش Founder)
    public function founders(): BelongsToMany
    {
        $founderRoleId = Role::where('name_en', 'Founder')->value('id');

        return $this->belongsToMany(User::class, 'institute_role_user')
            ->withPivot(['role_id', 'assigned_by'])
            ->wherePivot('role_id', $founderRoleId)
            ->withTimestamps();
    }

    // فیلتر کاربران با نقش خاص)
    public function usersByRole($roleName): BelongsToMany
    {
        $roleId = Role::where('name_en', $roleName)->value('id');

        return $this->belongsToMany(User::class, 'institute_role_user')
            ->withPivot(['role_id', 'assigned_by'])
            ->wherePivot('role_id', $roleId)
            ->withTimestamps();
    }

}
