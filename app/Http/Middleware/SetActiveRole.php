<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetActiveRole
{
    public function handle(Request $request, Closure $next)
    {
        // کاربر وارد شده و session نقش فعال ندارد
        if (Auth::check() && !session()->has('active_role_id')) {

            // گرفتن نقش‌ها و آموزشگاه‌ها
            $roles = Auth::user()->getAllRolesWithInstitutes();

            if ($roles->count() === 1) {
                // فقط یک نقش → ست خودکار
                $role = $roles->first();
                session([
                    'active_role_id' => $role->role_id,
                    'active_institute_id' => $role->institute_id,
                ]);
            } elseif ($roles->count() > 1) {
                // چند نقش → هدایت به صفحه انتخاب نقش
                if (!$request->routeIs('select_role')) {
                    return redirect()->route('select_role');
                }
            }
        }

        return $next($request);
    }
}
