<?php

namespace App\Helpers;

use App\Models\PageVisitLog;
use Illuminate\Support\Facades\Request;

class PageVisitHelper
{
    /**
     * ثبت بازدید صفحه با جلوگیری از بازدید تکراری همان روز
     *
     * @param string $pageName
     * @return void
     */
    public static function register(string $pageName): void
    {
        $ip = Request::ip();
        $userAgent = Request::header('User-Agent');
        $today = now()->toDateString();

        // بررسی اینکه کاربر امروز قبلا بازدید کرده یا نه
        $alreadyVisited = PageVisitLog::where('page', $pageName)
            ->where('ip', $ip)
            ->where('visit_date', $today)
            ->exists();

        if (!$alreadyVisited) {
            PageVisitLog::create([
                'page' => $pageName,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'visit_date' => $today
            ]);
        }
    }

    /**
     * گرفتن تعداد بازدید واقعی یک صفحه
     *
     * @param string $pageName
     * @return int
     */
    public static function count(string $pageName): int
    {
        return PageVisitLog::where('page', $pageName)->count();
    }
}
