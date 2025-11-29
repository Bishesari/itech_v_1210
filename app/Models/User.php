<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    protected $fillable = ['user_name', 'password'];
    protected $hidden = ['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'];
    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    public function profile():HasOne
    {
        return $this->hasOne(Profile::class);
    }
    public function contacts():belongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }

    // برای انتخاب نقش بعد از لاگین کردن نیاز دارم، اطلاعات اسم فارسی نقش و نام کوتاه آموزشگاه رو نیاز دارم.
    public function getAllRolesWithInstitutes(): Collection
    {
        return DB::table('institute_role_user')
            ->join('roles', 'institute_role_user.role_id', '=', 'roles.id')
            ->leftJoin('institutes', 'institute_role_user.institute_id', '=', 'institutes.id')
            ->where('institute_role_user.user_id', $this->id)
            ->select(
                'roles.id as role_id', 'roles.name_fa as role_name', 'institutes.id as institute_id', 'institutes.short_name as institute_name'
            )
            ->get();
    }

}
