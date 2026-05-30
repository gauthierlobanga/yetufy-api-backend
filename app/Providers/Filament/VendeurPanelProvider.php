<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureTenantSubscription;
use App\Http\Middleware\EnsureUserIsVendeur;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class VendeurPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendeur')
            ->path('vendeur')
            ->viteTheme('resources/css/filament/admin/theme.css')
            // ->brandLogo(fn () => view('filament.admin.logo'))
            ->font('inter')
            ->sidebarWidth('16rem')
            ->profile()
            ->login()
            ->navigationGroups(groups: [
                NavigationGroup::make()
                    ->label('Market')
                    ->icon(Heroicon::ShoppingBag),
                NavigationGroup::make()
                    ->label('Blog')
                    ->icon(Heroicon::Newspaper),
                NavigationGroup::make()
                    ->label('Contact')
                    ->icon(Heroicon::Inbox),
                NavigationGroup::make()
                    ->label('About')
                    ->icon(Heroicon::OutlinedInformationCircle),
                NavigationGroup::make()
                    ->label('Help')
                    ->icon(Heroicon::InformationCircle),
                NavigationGroup::make()
                    ->label('Parametrises')
                    ->icon(Heroicon::Cog8Tooth),
                NavigationGroup::make()
                    ->label('Comptes')
                    ->icon(Heroicon::UserGroup),
                NavigationGroup::make()
                    ->label('Organisation')
                    ->icon(Heroicon::BuildingOffice),
                NavigationGroup::make()
                    ->label('Tenants')
                    ->icon(Heroicon::BuildingOffice),
                NavigationGroup::make()
                    ->label('Clients')
                    ->icon(Heroicon::UserGroup),
                NavigationGroup::make()
                    ->label('Fournisseurs')
                    ->icon(Heroicon::Truck),
                NavigationGroup::make()
                    ->label('Core')
                    ->icon(Heroicon::Cog6Tooth),
                NavigationGroup::make()
                    ->label('Filament Shield')
                    ->icon(Heroicon::ShieldCheck),
                NavigationGroup::make()
                    ->label('Notifications')
                    ->icon(Heroicon::Bell),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('9rem')
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Emerald,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Vendeur/Resources'), for: 'App\\Filament\\Vendeur\\Resources')
            ->discoverPages(in: app_path('Filament/Vendeur/Pages'), for: 'App\\Filament\\Vendeur\\Pages')
            ->discoverWidgets(in: app_path('Filament/Vendeur/Widgets'), for: 'App\\Filament\\Vendeur\\Widgets')
            ->discoverClusters(in: app_path('Filament/Vendeur/Clusters'), for: 'App\\Filament\\Vendeur\\Clusters')
            ->pages([
                Dashboard::class,
            ])
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
                EnsureTenantSubscription::class,
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
            ], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsVendeur::class,
            ])
            ->plugins(plugins: [
                FilamentShieldPlugin::make()
                    ->navigationLabel('Bouclier')                  // string|Closure|null
                    ->navigationIcon('heroicon-o-home')         // string|Closure|null
                    ->activeNavigationIcon('heroicon-s-home')   // string|Closure|null
                    ->navigationSort(10)                        // int|Closure|null
                    ->navigationBadge()                      // string|Closure|null
                    ->globallySearchable(true)                  // bool|Closure
                    ->globalSearchResultsLimit(50)              // int|Closure
                    ->navigationBadgeColor('success')           // string|Closure|null
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
            ->resourceEditPageRedirect('index')
            ->databaseNotifications()
            ->resourceCreatePageRedirect('index');
    }
}
