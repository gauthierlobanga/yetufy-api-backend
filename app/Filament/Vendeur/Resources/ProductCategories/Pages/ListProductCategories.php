<?php

namespace App\Filament\Vendeur\Resources\ProductCategories\Pages;

use App\Filament\Vendeur\Resources\ProductCategories\ProductCategoryResource;
use App\Models\ProductCategory;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // Tous les Categorys
            'all' => Tab::make('Tous')
                ->badge(ProductCategory::count())
                ->badgeColor('gray')
                ->icon('heroicon-m-document-text')
                ->iconPosition(IconPosition::Before),

            // ProductCategorys Active
            'published' => Tab::make('Active')
                ->badge(ProductCategory::where('est_active', true)->count())
                ->badgeColor('success')
                ->icon('heroicon-m-check-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('est_active', true)),

            'separator' => Tab::make('')
                ->visible(false),

            // ProductCategorys de cette semaine
            'this_week' => Tab::make('Cette semaine')
                ->badge(ProductCategory::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),

            // ProductCategorys du mois dernier
            'last_month' => Tab::make('Mois dernier')
                ->badge(ProductCategory::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar-days')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])),

            // ProductCategorys des 30 derniers jours
            'last_30_days' => Tab::make('30 derniers jours')
                ->badge(ProductCategory::where('created_at', '>=', now()->subDays(30))->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30))),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'published';
    }
}
