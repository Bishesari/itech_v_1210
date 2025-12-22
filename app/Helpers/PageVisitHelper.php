<?php

namespace App\Helpers;

use App\Models\PageVisitLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class PageVisitHelper
{
    public static function resolvePageKey(): string
    {
        $routeName = Route::currentRouteName();

        if ($routeName) {
            $params = request()->route()?->parameters() ?? [];

            if (!empty($params)) {
                return $routeName . ':' . implode('-', $params);
            }

            return $routeName;
        }

        // fallback
        return trim(request()->path(), '/');
    }

    public static function register(): void
    {
        $pageKey = self::resolvePageKey();

        $ip = request()->ip();
        $today = now()->toDateString();

        $exists = PageVisitLog::where('page', $pageKey)
            ->where('ip', $ip)
            ->where('visit_date', $today)
            ->exists();

        if (!$exists) {
            PageVisitLog::create([
                'page' => $pageKey,
                'ip' => $ip,
                'user_agent' => request()->userAgent(),
                'visit_date' => $today,
            ]);
        }
    }
    public static function count(): int
    {
        return PageVisitLog::where('page', self::resolvePageKey())->count();
    }
}
