<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher
{
    private string $cookieName = 'erp_locale';

    public function supportedLocales(): array
    {
        return config('app.available_locales', ['id', 'en']);
    }

    public function switchTo(string $locale): bool
    {
        if (! $this->isSupported($locale)) {
            return false;
        }

        Session::put('locale', $locale);
        Cookie::queue($this->cookieName, $locale, 60 * 24 * 365);
        App::setLocale($locale);

        return true;
    }

    public function getLocaleFromRequest(Request $request): string
    {
        $locale = $request->query('lang')
            ?: Session::get('locale')
            ?: $request->cookie($this->cookieName)
            ?: config('app.locale', 'id');

        return $this->isSupported($locale) ? $locale : config('app.fallback_locale', 'en');
    }

    public function isSupported(?string $locale): bool
    {
        return in_array($locale, $this->supportedLocales(), true);
    }
}
