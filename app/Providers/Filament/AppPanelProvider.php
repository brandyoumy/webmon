<?php

namespace App\Providers\Filament;

use App\Filament\Helper\CustomLogin;
use App\Filament\Widgets\LatestSSL;
use App\Filament\Widgets\LatestStatus;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\AuthUiEnhancerLogin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Andreia\FilamentNordTheme\FilamentNordThemePlugin;


class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/')
            ->defaultThemeMode(ThemeMode::Dark)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->plugins([
                AuthUIEnhancerPlugin::make()
                ->showEmptyPanelOnMobile(false)
                ->formPanelPosition('left')
                ->emptyPanelBackgroundImageOpacity('80%')
                ->emptyPanelBackgroundImageUrl(asset('img/bg_webmon.jpg'))
            ])
            ->favicon(asset('img/webmonicon.PNG'))
            ->brandName('Web Monitoring (Webmon)')
            ->plugin(FilamentNordThemePlugin::make())
            ->sidebarCollapsibleOnDesktop()
            ->login(CustomLogin::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->routes(function () {
                \Illuminate\Support\Facades\Route::get('/two-factor-verify', \App\Filament\Pages\Auth\TwoFactorVerify::class)
                    ->name('pages.two-factor-verify');
            })
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                LatestStatus::class,
                LatestSSL::class
                // AccountWidget::class,
                // FilamentInfoWidget::class,
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
                \App\Http\Middleware\RequireTwoFactor::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
  
}
