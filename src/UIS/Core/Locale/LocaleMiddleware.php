<?php

namespace UIS\Core\Locale;

use Closure;
use Illuminate\Contracts\Routing\Middleware;
use Lang;

class LocaleMiddleware implements Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Lang::detectLanguage();

        return $next($request);
    }
}
