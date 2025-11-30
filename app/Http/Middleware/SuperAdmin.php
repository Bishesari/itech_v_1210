<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // فرض می‌کنیم Role::where('name_en','SuperAdmin') موجود است
        $superAdminRoleId = \App\Models\Role::where('name_en', 'SuperAdmin')->value('id');

        // نقش فعال کاربر
        $activeRoleId = session('active_role_id');

        if ($activeRoleId !== $superAdminRoleId) {
            abort(403, 'دسترسی غیرمجاز');
        }
        return $next($request);
    }
}
