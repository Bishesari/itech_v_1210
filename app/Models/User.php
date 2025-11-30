<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasJalaliDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasJalaliDates;
    protected $fillable = ['type', 'user_name', 'password'];
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];
    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
    public function initials(): string
    {
        if ($this->profile()->exists()) {
            if ($this->profile->f_name_fa){
                return Str::of($this->profile->f_name_fa . '، ' . $this->profile->l_name_fa)
                    ->explode('، ')
                    ->map(fn(string $name) => Str::of($name)->substr(0, 1))
                    ->implode(' ');
            }
        }
        return Str::of($this->user_name)->substr(0, 2);

        /*
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
        */
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
    public function getAllRolesWithInstitutes()
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

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_user')
            ->withPivot(['id', 'started_at', 'finished_at', 'score', 'question_order'])
            ->withTimestamps();
    }

    // کاربر در چند آموزشگاه نقش دارد
    public function institutes(): BelongsToMany
    {
        return $this->belongsToMany(Institute::class, 'institute_role_user')
            ->withPivot(['role_id', 'assigned_by'])
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'institute_role_user')
            ->withPivot(['institute_id', 'assigned_by'])
            ->withTimestamps();
    }
    // نقش‌های کاربر همراه با اطلاعات آموزشگاه
    public function institutesWithRoles()
    {
        return DB::table('institute_role_user')
            ->join('institutes', 'institute_role_user.institute_id', '=', 'institutes.id')
            ->join('roles', 'institute_role_user.role_id', '=', 'roles.id')
            ->where('institute_role_user.user_id', $this->id)
            ->select(
                'institute_role_user.*',
                'institutes.short_name as institute_name',
                'roles.name_en as role_name_en',
                'roles.name_fa as role_name_fa'
            )
            ->get();
    }

    public function rolesInActiveInstitute()
    {
        $activeInstituteId = session('active_institute_id');

        return DB::table('institute_role_user')
            ->join('institutes', 'institute_role_user.institute_id', '=', 'institutes.id')
            ->join('roles', 'institute_role_user.role_id', '=', 'roles.id')
            ->where('institute_role_user.user_id', $this->id)
            ->where('institute_role_user.institute_id', $activeInstituteId)
            ->select(
                'institute_role_user.*',
                'institutes.short_name as institute_name',
                'roles.name_en as role_name_en',
                'roles.name_fa as role_name_fa'
            )
            ->get();
    }

    public function groupedRoles()
    {
        $roles = $this->belongsToMany(Role::class, 'institute_role_user')
            ->withPivot(['institute_id'])
            ->get();
        // همه institute ها را یکجا بگیر
        $instituteIds = $roles->pluck('pivot.institute_id')->filter()->unique();
        $institutes = Institute::whereIn('id', $instituteIds)->get()->keyBy('id');
        return [
            'roles'      => $roles,
            'institutes' => $institutes,
            'grouped'    => $roles->groupBy(fn($role) => $role->pivot->institute_id ?? 'global'),
        ];
    }

}
