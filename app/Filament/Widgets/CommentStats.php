<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommentStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        return [
            Stat::make('Total commentaires', Comment::count())
                ->description('Tous statuts confondus')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),

            Stat::make('En attente', Comment::where('status', Comment::STATUS_PENDING)->count())
                ->description('À modérer')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Approuvés', Comment::where('status', Comment::STATUS_APPROVED)->count())
                ->description('Commentaires visibles')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Signalés', Comment::where('reports_count', '>', 0)->count())
                ->description('À examiner')
                ->descriptionIcon('heroicon-m-flag')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
