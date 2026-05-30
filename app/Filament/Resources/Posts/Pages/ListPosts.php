<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // Tous les posts
            'all' => Tab::make('Tous')
                ->badge(Post::count())
                ->badgeColor('gray')
                ->icon('heroicon-m-document-text')
                ->iconPosition(IconPosition::Before),

            // Posts publiés
            'published' => Tab::make('Publiés')
                ->badge(Post::where('status', Post::STATUS_PUBLISHED)->count())
                ->badgeColor('success')
                ->icon('heroicon-m-check-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Post::STATUS_PUBLISHED)),

            // Posts en brouillon
            'draft' => Tab::make('Brouillons')
                ->badge(Post::where('status', Post::STATUS_DRAFT)->count())
                ->badgeColor('gray')
                ->icon('heroicon-m-pencil')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Post::STATUS_DRAFT)),

            // Posts programmés
            'scheduled' => Tab::make('Programmés')
                ->badge(Post::where('status', Post::STATUS_SCHEDULED)->count())
                ->badgeColor('warning')
                ->icon('heroicon-m-clock')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Post::STATUS_SCHEDULED)),

            // Posts expirés
            'expired' => Tab::make('Expirés')
                ->badge(Post::where('status', Post::STATUS_EXPIRED)->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-x-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Post::STATUS_EXPIRED)),

            // Posts archivés
            'archived' => Tab::make('Archivés')
                ->badge(Post::where('status', Post::STATUS_ARCHIVED)->count())
                ->badgeColor('gray')
                ->icon('heroicon-m-archive-box')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Post::STATUS_ARCHIVED)),

            // Séparateur visuel (optionnel)
            'separator' => Tab::make('')
                ->visible(false),

            // Posts de cette semaine
            'this_week' => Tab::make('Cette semaine')
                ->badge(Post::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),

            // Posts du mois dernier
            'last_month' => Tab::make('Mois dernier')
                ->badge(Post::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar-days')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])),

            // Posts des 30 derniers jours
            'last_30_days' => Tab::make('30 derniers jours')
                ->badge(Post::where('created_at', '>=', now()->subDays(30))->count())
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
