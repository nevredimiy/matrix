<?php

namespace App\Providers\Filament;

use App\Filament\Pages\FactoryMissingPage;
use App\Filament\Pages\FactoryOrderItem2;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\AdminResource\Widgets\OrdersProductsTable;
use App\Filament\Resources\FactoryOrderItemResource\Widgets\StatsProductOverview;
use App\Filament\Widgets\FactoryOrderItemStats;
use App\Models\FactoryOrderItem;
use Filament\Navigation\NavigationGroup;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->profile()            
            ->login()
            ->registration()
            ->passwordReset()
            ->brandName('Dinara CRTM')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('50px')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,

                OrdersProductsTable::class,
                FactoryOrderItemStats::class,
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
            ])
            ->pages([
                FactoryMissingPage::class,
            ])
            
            ->navigationItems([              
                NavigationItem::make('Заказы - Пр. №1')
                    ->url(fn () => FactoryMissingPage::getUrl(['factory_id' => 1]))
                    ->icon('heroicon-o-truck')
                    ->sort(10)
                    ->group('Производство')
                    ->badge(function () {
                        return FactoryOrderItem::query()
                            ->whereHas('factoryOrder', function ($q) {
                                $q->where('factory_id', 1);
                            })
                            ->distinct('product_id')
                            ->count();
                    }),

                NavigationItem::make('Заказы - Пр. №2')
                    ->url(fn () => FactoryMissingPage::getUrl(['factory_id' => 2]))
                    ->icon('heroicon-o-truck')
                    ->sort(11)
                    ->group('Производство')
                    ->badge(function () {
                        return FactoryOrderItem::query()
                            ->whereHas('factoryOrder', function ($q) {
                                $q->where('factory_id', 2);
                            })
                            ->distinct('product_id')
                            ->count();
                    }),                    
            ]);            
        
    }

}
