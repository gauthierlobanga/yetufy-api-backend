<?php

namespace App\Filament\Vendeur\Resources\Comments\Pages;

use App\Filament\Vendeur\Resources\Comments\CommentResource;
use App\Filament\Vendeur\Widgets\CommentStats;
use App\Models\Comment;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;

class EditComment extends EditRecord
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CommentStats::class,
        ];
    }

    public function getTabs(): array
    {

        return [
            // Tous les Comments
            'all' => Tab::make('Tous')
                ->badge(Comment::count())
                ->badgeColor('gray')
                ->icon('heroicon-m-document-text')
                ->iconPosition(IconPosition::Before),

            // Comments récents (7 derniers jours)
            'recent' => Tab::make('Récents')
                ->badge(Comment::where('created_at', '>=', now()->subDays(7))->count())
                ->badgeColor('primary')
                ->icon('heroicon-m-clock')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7))),

            // Comments publiés
            'published' => Tab::make('Approuvés')
                ->badge(Comment::where('status', Comment::STATUS_APPROVED)->count())
                ->badgeColor('success')
                ->icon('heroicon-m-check-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Comment::STATUS_APPROVED)),

            // Comments programmés
            'scheduled' => Tab::make('En cours')
                ->badge(Comment::where('status', Comment::STATUS_PENDING)->count())
                ->badgeColor('warning')
                ->icon('heroicon-m-clock')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Comment::STATUS_PENDING)),

            // Comments expirés
            'expired' => Tab::make('Supprimé')
                ->badge(Comment::where('status', Comment::STATUS_TRASHED)->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-x-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Comment::STATUS_TRASHED)),

            'expired' => Tab::make('Signalés')
                ->badge(Comment::where('status', Comment::STATUS_SPAM)->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-x-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Comment::STATUS_SPAM)),

            // Séparateur visuel (optionnel)
            'separator' => Tab::make('')
                ->visible(false),

            // Posts de cette semaine
            'this_week' => Tab::make('Cette semaine')
                ->badge(Comment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),

            // Comments du mois dernier
            'last_month' => Tab::make('Mois dernier')
                ->badge(Comment::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar-days')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])),

            // Comments des 30 derniers jours
            'last_30_days' => Tab::make('30 derniers jours')
                ->badge(Comment::where('created_at', '>=', now()->subDays(30))->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30))),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'recent';
    }
}
