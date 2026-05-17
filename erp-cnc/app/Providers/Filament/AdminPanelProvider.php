<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\CncFlowStatsWidget;
use App\Filament\Widgets\JobOrderChartWidget;
use App\Filament\Widgets\OverdueInvoicesWidget;
use App\Filament\Widgets\QuotationConversionChartWidget;
use App\Filament\Widgets\RecentActivitiesWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('ERP CNC')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('2.25rem')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->spa()
            ->colors([
                'primary' => '#1a1917',
                'secondary' => '#6b6860',
                'info' => '#185fa5',
                'success' => '#3b6d11',
                'warning' => '#854f0b',
                'danger' => '#a32d2d',
            ])
            ->font('DM Sans')
            ->darkMode(true)
            ->defaultThemeMode(ThemeMode::Light)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                CncFlowStatsWidget::class,
                RevenueChartWidget::class,
                JobOrderChartWidget::class,
                QuotationConversionChartWidget::class,
                RecentActivitiesWidget::class,
                OverdueInvoicesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
