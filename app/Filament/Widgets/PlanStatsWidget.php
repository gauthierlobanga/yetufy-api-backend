<?php

namespace App\Filament\Widgets;

use App\Models\Plan;
use App\Models\VendorRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlanStatsWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        return [
            // 1. Total des plans
            Stat::make('Total des plans', Plan::count())
                ->description('Tous les plans confondus')
                ->descriptionIcon('heroicon-o-inbox-stack')
                ->color('primary'),

            // 2. Plans actifs
            Stat::make('Plans actifs', Plan::where('is_active', true)->count())
                ->description('Disponibles pour les nouveaux vendeurs')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            // 3. Plans en vedette
            Stat::make('Plans vedettes', Plan::where('is_featured', true)->count())
                ->description('Mis en avant sur la page d\'inscription')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),

            // 4. Plans gratuits
            Stat::make('Plans gratuits', Plan::where('price', 0)->count())
                ->description('Pour démarrer sans engagement')
                ->descriptionIcon('heroicon-o-gift')
                ->color('info'),

            // 5. Plans payants
            Stat::make('Plans payants', Plan::where('price', '>', 0)->count())
                ->description('Avec fonctionnalités avancées')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('emerald'),

            // 6. Taux de conversion (demandes → approbations)
            Stat::make(
                'Taux de conversion',
                $this->calculateConversionRate().'%'
            )
                ->description('Demandes approuvées / total traitées')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('purple'),

            // 7. Demandes en attente
            Stat::make(
                'Demandes en attente',
                VendorRequest::whereIn('status', ['pending', 'payment_pending'])->count()
            )
                ->description('À traiter')
                ->descriptionIcon('heroicon-o-clock')
                ->color('danger'),

            // 8. Total demandes
            Stat::make(
                'Total demandes',
                VendorRequest::count()
            )
                ->description('Cumulées depuis le lancement')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    private function calculateConversionRate(): float
    {
        $total = VendorRequest::whereIn('status', ['approved', 'rejected'])->count();

        if ($total === 0) {
            return 0;
        }

        $approved = VendorRequest::where('status', 'approved')->count();

        return round(($approved / $total) * 100, 1);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }
}
