<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureCentralDomain;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
// use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('4rem')
            // ->favicon(Storage::url('images/favicon.ico'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Emerald,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestsDuringMaintenance::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([
                'universal',
                EnsureCentralDomain::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationLabel('Bouclier')
                    ->navigationIcon(Heroicon::OutlinedShieldCheck)
                    ->activeNavigationIcon(Heroicon::ShieldCheck)
                    ->navigationSort(10)
                    ->navigationBadge()
                    ->globallySearchable(true)
                    ->globalSearchResultsLimit(50)
                    ->navigationBadgeColor('success')
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsSuperAdmin::class,
            ])
            ->databaseNotifications()
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index');
    }
}
