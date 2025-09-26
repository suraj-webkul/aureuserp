<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Webkul\Support\Filament\Pages\Profile;
use Webkul\Support\PluginManager;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        set_time_limit(300);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('images/favicon.ico'))
            ->brandLogo(asset('images/logo-light.svg'))
            ->darkModeBrandLogo(asset('images/logo-dark.svg'))
            ->brandLogoHeight('2rem')
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->unsavedChangesAlerts()
            ->topNavigation()
            ->maxContentWidth(Width::Full)
            ->userMenuItems([
                'profile' => Action::make('profile')
                    ->label(fn () => filament()->auth()->user()?->name)
                    ->url(fn (): string => Profile::getUrl()),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Dashboard')
                    ->icon('icon-dashboard'),
                NavigationGroup::make()
                    ->label('Contact')
                    ->icon('icon-contacts'),
                NavigationGroup::make()
                    ->label('Sales')
                    ->icon('icon-sales'),
                NavigationGroup::make()
                    ->label('Purchase')
                    ->icon('icon-purchases'),
                NavigationGroup::make()
                    ->label('Invoices')
                    ->icon('icon-invoices'),
                NavigationGroup::make()
                    ->label('Inventory')
                    ->icon('icon-inventories'),
                NavigationGroup::make()
                    ->label('Project')
                    ->icon('icon-projects'),
                NavigationGroup::make()
                    ->label('Employees')
                    ->icon('icon-employees'),
                NavigationGroup::make()
                    ->label('Time Off')
                    ->icon('icon-time-offs'),
                NavigationGroup::make()
                    ->label('Recruitments')
                    ->icon('icon-recruitments'),
                NavigationGroup::make()
                    ->label('Website')
                    ->icon('icon-website'),
                NavigationGroup::make()
                    ->label('Settings')
                    ->icon('icon-settings'),
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm'      => 1,
                        'lg'      => 2,
                        'xl'      => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm'      => 1,
                        'lg'      => 2,
                        'xl'      => 3,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm'      => 2,
                    ]),
                PluginManager::make(),
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
