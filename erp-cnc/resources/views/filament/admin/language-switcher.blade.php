@php
    $currentLocale = app()->getLocale();
    $locales = config('app.available_locales', ['id', 'en']);
@endphp

<div class="erp-language-switcher" title="{{ __('app.language') }}">
    <button type="button" class="erp-language-switcher__trigger" aria-label="{{ __('app.language') }}">
        <span class="erp-language-switcher__flag">{{ $currentLocale === 'id' ? 'ID' : 'EN' }}</span>
        <span class="erp-language-switcher__label">{{ __('app.locales.' . $currentLocale) }}</span>
        <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
            <path d="m6 8 4 4 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div class="erp-language-switcher__menu">
        @foreach ($locales as $locale)
            <a
                href="{{ route('lang.switch', $locale) }}"
                class="erp-language-switcher__item {{ $currentLocale === $locale ? 'is-active' : '' }}"
                @if ($currentLocale === $locale) aria-current="true" @endif
            >
                <span>{{ strtoupper($locale) }}</span>
                <strong>{{ __('app.locales.' . $locale) }}</strong>
            </a>
        @endforeach
    </div>
</div>
