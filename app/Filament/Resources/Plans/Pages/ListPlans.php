<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use App\Filament\Widgets\PlanStatsWidget;
use App\Models\Plan;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PlanStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous')
                ->icon('heroicon-o-inbox-stack')
                ->badge(Plan::count()),

            'active' => Tab::make('Actifs')
                ->icon('heroicon-o-check-circle')
                ->badge(Plan::where('is_active', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', true)),

            'inactive' => Tab::make('Inactifs')
                ->icon('heroicon-o-pause-circle')
                ->badge(Plan::where('is_active', false)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn ($query) => $query->where('is_active', false)),

            'featured' => Tab::make('Vedettes')
                ->icon('heroicon-o-star')
                ->badge(Plan::where('is_featured', true)->count())
                ->badgeColor('amber')
                ->modifyQueryUsing(fn ($query) => $query->where('is_featured', true)),

            'recommended' => Tab::make('Recommandés')
                ->icon('heroicon-o-hand-thumb-up')
                ->badge(Plan::where('is_recommended', true)->count())
                ->badgeColor('purple')
                ->modifyQueryUsing(fn ($query) => $query->where('is_recommended', true)),

            'free' => Tab::make('Gratuits')
                ->icon('heroicon-o-gift')
                ->badge(Plan::where('price', 0)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('price', 0)),

            'paid' => Tab::make('Payants')
                ->icon('heroicon-o-banknotes')
                ->badge(Plan::where('price', '>', 0)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn ($query) => $query->where('price', '>', 0)),

            'monthly' => Tab::make('Mensuels')
                ->icon('heroicon-o-calendar-days')
                ->badge(Plan::where('interval', 'month')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->where('interval', 'month')),

            'annual' => Tab::make('Annuels')
                ->icon('heroicon-o-calendar')
                ->badge(Plan::where('interval', 'year')->count())
                ->badgeColor('emerald')
                ->modifyQueryUsing(fn ($query) => $query->where('interval', 'year')),

            'trashed' => Tab::make('Corbeille')
                ->icon('heroicon-o-trash')
                ->badge(Plan::onlyTrashed()->count())
                ->badgeColor('red')
                ->modifyQueryUsing(fn ($query) => $query->onlyTrashed()),
        ];
    }
}
