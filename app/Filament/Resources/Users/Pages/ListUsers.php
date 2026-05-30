<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // Tous les Users
            'all' => Tab::make('Tous')
                ->badge(User::count())
                ->deferBadge()
                ->badgeColor('gray')
                ->icon('heroicon-m-user-group')
                ->iconPosition(IconPosition::Before),
            // Tous les Users
            'Actif' => Tab::make('Actif')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->excludeQueryWhenResolvingRecord()
                ->badge(User::query()->where('is_active', true)->count())
                ->deferBadge()
                ->badgeColor('success')
                ->icon('heroicon-m-user-group')
                ->iconPosition(IconPosition::Before),
            // Tous les Users
            'Inactif' => Tab::make('Inactif')
                ->badge(User::query()->where('is_active', false)->count())
                ->deferBadge()
                ->badgeColor('danger')
                ->icon('heroicon-m-user-group')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->excludeQueryWhenResolvingRecord(),

            // Séparateur visuel (optionnel)
            'separator' => Tab::make('')
                ->visible(false),

            // Users de cette semaine
            'this_week' => Tab::make('Cette semaine')
                ->badge(User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('info')
                ->deferBadge()
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->excludeQueryWhenResolvingRecord(),

            // Users du mois dernier
            'last_month' => Tab::make('Mois dernier')
                ->badge(User::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count())
                ->badgeColor('info')
                ->deferBadge()
                ->icon('heroicon-m-calendar-days')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]))
                ->excludeQueryWhenResolvingRecord(),

            // Users des 30 derniers jours
            'last_30_days' => Tab::make('30 derniers jours')
                ->badge(User::where('created_at', '>=', now()->subDays(30))->count())
                ->badgeColor('info')
                ->deferBadge()
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30)))
                ->excludeQueryWhenResolvingRecord(),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'Actif';
    }
}
