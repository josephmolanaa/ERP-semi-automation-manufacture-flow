<?php

namespace App\Http\Middleware;

use App\Services\LanguageSwitcher;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(app(LanguageSwitcher::class)->getLocaleFromRequest($request));

        return $next($request);
    }
}
