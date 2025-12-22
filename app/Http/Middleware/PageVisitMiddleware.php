<?php
namespace App\Http\Middleware;

use Closure;
use App\Helpers\PageVisitHelper;

class PageVisitMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('get')) {
            PageVisitHelper::register();
        }

        return $next($request);
    }
}
