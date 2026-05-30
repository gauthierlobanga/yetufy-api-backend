<?php

namespace App\Filament\Widgets;

use App\Models\Contact;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContactStatsWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        return [
            Stat::make('Messages reçus', Contact::count())
                ->description('Total des messages')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('primary'),

            Stat::make('En attente', Contact::where('status', 'en_attente')->count())
                ->description('À traiter')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Non traités', Contact::whereIn('status', ['en_attente', 'lu'])->count())
                ->description('En cours de traitement')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info'),

            Stat::make('Urgents', Contact::where('priorite', 'urgente')
                ->whereIn('status', ['en_attente', 'lu'])
                ->count())
                ->description('À traiter en priorité')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Répondus', Contact::where('status', 'repondu')->count())
                ->description('Traités avec succès')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Temps moyen réponse', function () {
                // Récupérer les paires created_at / repondu_at pour les contacts répondus
                $times = Contact::whereNotNull('repondu_at')
                    ->select(['created_at', 'repondu_at'])
                    ->get();

                if ($times->isEmpty()) {
                    return 'N/A';
                }

                // Calculer la moyenne des différences en secondes
                $totalSeconds = 0;
                $count = 0;

                foreach ($times as $time) {
                    $created = Carbon::parse($time->created_at);
                    $repondu = Carbon::parse($time->repondu_at);
                    $totalSeconds += $created->diffInSeconds($repondu);
                    $count++;
                }

                $avgSeconds = $totalSeconds / $count;

                $hours = floor($avgSeconds / 3600);
                $minutes = floor(($avgSeconds % 3600) / 60);

                if ($hours > 0) {
                    return "{$hours}h {$minutes}min";
                }

                return "{$minutes}min";
            })
                ->description('Délai moyen de réponse')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
