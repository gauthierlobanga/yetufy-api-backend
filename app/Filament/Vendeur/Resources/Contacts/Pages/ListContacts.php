<?php

namespace App\Filament\Vendeur\Resources\Contacts\Pages;

use App\Filament\Vendeur\Resources\Contacts\ContactResource;
use App\Filament\Widgets\ContactStatsWidget;
use App\Models\Contact;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListContacts extends ListRecords
{
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ContactStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            // Tous les contacts
            'all' => Tab::make('Tous')
                ->badge(Contact::count())
                ->badgeColor('gray')
                ->icon('heroicon-m-document-text')
                ->iconPosition(IconPosition::Before),

            // Nouveaux messages (non lus)
            'nouveaux' => Tab::make('Nouveaux')
                ->badge(Contact::where('status', 'en_attente')->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-envelope')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'en_attente')),

            // Messages récents (7 derniers jours)
            'recents' => Tab::make('Récents')
                ->badge(Contact::where('created_at', '>=', now()->subDays(7))->count())
                ->badgeColor('primary')
                ->icon('heroicon-m-clock')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7))),

            // Messages lus
            'lus' => Tab::make('Lus')
                ->badge(Contact::where('status', 'lu')->count())
                ->badgeColor('info')
                ->icon('heroicon-m-eye')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'lu')),

            // Messages répondus
            'repondus' => Tab::make('Répondus')
                ->badge(Contact::where('status', 'repondu')->count())
                ->badgeColor('success')
                ->icon('heroicon-m-check-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'repondu')),

            // Messages archivés
            'archives' => Tab::make('Archivés')
                ->badge(Contact::where('status', 'archive')->count())
                ->badgeColor('gray')
                ->icon('heroicon-m-archive-box')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'archive')),

            // Messages spam
            'spams' => Tab::make('Spams')
                ->badge(Contact::where('status', 'spam')->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-no-symbol')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'spam')),

            // Séparateur visuel
            'separator' => Tab::make('')
                ->visible(false),

            // Messages urgents
            'urgents' => Tab::make('Urgents')
                ->badge(Contact::where('priorite', 'urgente')
                    ->whereIn('status', ['en_attente', 'lu'])
                    ->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-exclamation-triangle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('priorite', 'urgente')
                    ->whereIn('status', ['en_attente', 'lu'])),

            // Messages de cette semaine
            'cette_semaine' => Tab::make('Cette semaine')
                ->badge(Contact::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),

            // Messages du mois dernier
            'mois_dernier' => Tab::make('Mois dernier')
                ->badge(Contact::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar-days')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])),

            // Messages des 30 derniers jours
            '30_derniers_jours' => Tab::make('30 derniers jours')
                ->badge(Contact::where('created_at', '>=', now()->subDays(30))->count())
                ->badgeColor('info')
                ->icon('heroicon-m-calendar')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30))),

            // Par catégorie - Général
            'categorie_general' => Tab::make('Général')
                ->badge(Contact::where('categorie', 'general')
                    ->whereIn('status', ['en_attente', 'lu'])
                    ->count())
                ->badgeColor('gray')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('categorie', 'general')),

            // Par catégorie - Commercial
            'categorie_commercial' => Tab::make('Commercial')
                ->badge(Contact::where('categorie', 'commercial')
                    ->whereIn('status', ['en_attente', 'lu'])
                    ->count())
                ->badgeColor('primary')
                ->icon('heroicon-m-briefcase')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('categorie', 'commercial')),

            // Par catégorie - Technique
            'categorie_technique' => Tab::make('Technique')
                ->badge(Contact::where('categorie', 'technique')
                    ->whereIn('status', ['en_attente', 'lu'])
                    ->count())
                ->badgeColor('info')
                ->icon('heroicon-m-cog-6-tooth')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('categorie', 'technique')),

            // Par catégorie - Support
            'categorie_support' => Tab::make('Support')
                ->badge(Contact::where('categorie', 'support')
                    ->whereIn('status', ['en_attente', 'lu'])
                    ->count())
                ->badgeColor('warning')
                ->icon(Heroicon::PhoneArrowUpRight)
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('categorie', 'support')),

            // Par catégorie - Réclamation
            'categorie_reclamation' => Tab::make('Réclamation')
                ->badge(Contact::where('categorie', 'reclamation')
                    ->whereIn('status', ['en_attente', 'lu'])
                    ->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-exclamation-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('categorie', 'reclamation')),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'nouveaux';
    }
}
