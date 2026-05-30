<?php

namespace App\Filament\Vendeur\Resources\CampagneMarketings\Tables;

use App\Models\Marketing;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CampagneMarketingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->type_label)
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'newsletter' => 'primary',
                        'promotion' => 'success',
                        'saisonniere' => 'warning',
                        'relance' => 'info',
                        'fidelisation' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'newsletter' => 'Newsletter',
                        'promotion' => 'Promotion',
                        'saisonniere' => 'Saisonnière',
                        'relance' => 'Relance',
                        'fidelisation' => 'Fidélisation',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('canal')
                    ->label('Canal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'primary',
                        'sms' => 'success',
                        'reseaux' => 'info',
                        'push' => 'warning',
                        'affiliation' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'reseaux' => 'Réseaux sociaux',
                        'push' => 'Push',
                        'affiliation' => 'Affiliation',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planifiee' => 'info',
                        'active' => 'success',
                        'terminee' => 'gray',
                        'annulee' => 'danger',
                        'en_pause' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planifiee' => 'Planifiée',
                        'active' => 'Active',
                        'terminee' => 'Terminée',
                        'annulee' => 'Annulée',
                        'en_pause' => 'En pause',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('budget')
                    ->label('Budget')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('warning')
                    ->toggleable(),

                TextColumn::make('statistiques.revenus')
                    ->label('Revenus')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->statistiques['revenus'] ?? 0)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('roi')
                    ->label('ROI')
                    ->suffix('%')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->getStateUsing(fn ($record) => $record->retour_investissement ?? 0)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('statistiques.envois')
                    ->label('Envois')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('statistiques.ouvertures')
                    ->label('Ouvertures')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('statistiques.taux_ouverture')
                    ->label('Taux ouverture')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color('info')
                    ->getStateUsing(fn ($record) => $record->statistiques['taux_ouverture'] ?? 0)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('statistiques.taux_conversion')
                    ->label('Taux conversion')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color('success')
                    ->getStateUsing(fn ($record) => $record->statistiques['taux_conversion'] ?? 0)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_active')
                    ->label('En cours')
                    ->boolean()
                    ->trueIcon('heroicon-o-play-circle')
                    ->falseIcon('heroicon-o-pause-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => $record->est_en_cours)
                    ->toggleable(),

                TextColumn::make('date_debut')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('date_fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(Marketing::getTypes())
                    ->multiple(),

                SelectFilter::make('canal')
                    ->label('Canal')
                    ->options(Marketing::getCanaux())
                    ->multiple(),

                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(Marketing::getStatuts())
                    ->multiple(),

                TernaryFilter::make('est_active')
                    ->label('Campagnes actives')
                    ->placeholder('Toutes')
                    ->trueLabel('Actives')
                    ->falseLabel('Terminées/Annulées')
                    ->queries(
                        true: fn (Builder $query) => $query->where('statut', 'active')
                            ->where('date_debut', '<=', now())
                            ->where(function ($q) {
                                $q->whereNull('date_fin')
                                    ->orWhere('date_fin', '>=', now());
                            }),
                        false: fn (Builder $query) => $query->whereIn('statut', ['terminee', 'annulee']),
                    ),

                TernaryFilter::make('has_budget')
                    ->label('Avec budget')
                    ->placeholder('Toutes')
                    ->trueLabel('Avec budget défini')
                    ->falseLabel('Sans budget')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('budget')->where('budget', '>', 0),
                        false: fn (Builder $query) => $query->where(function ($q) {
                            $q->whereNull('budget')->orWhere('budget', 0);
                        }),
                    ),

                Filter::make('date_range')
                    ->label('Période')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Du'),
                        DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date_debut', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date_fin', '<=', $date));
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Activer')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'planifiee')
                    ->action(function ($record) {
                        $record->lancer();
                        Notification::make()
                            ->success()
                            ->title('Campagne activée')
                            ->send();
                    }),

                Action::make('pause')
                    ->label('Mettre en pause')
                    ->icon('heroicon-m-pause')
                    ->color('warning')
                    ->visible(fn ($record) => $record->statut === 'active')
                    ->action(function ($record) {
                        $record->mettreEnPause();
                        Notification::make()
                            ->success()
                            ->title('Campagne mise en pause')
                            ->send();
                    }),

                Action::make('resume')
                    ->label('Reprendre')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_pause')
                    ->action(function ($record) {
                        $record->reprendre();
                        Notification::make()
                            ->success()
                            ->title('Campagne reprise')
                            ->send();
                    }),

                Action::make('terminate')
                    ->label('Terminer')
                    ->icon('heroicon-m-check-circle')
                    ->color('gray')
                    ->visible(fn ($record) => in_array($record->statut, ['active', 'en_pause']))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->terminer();
                        Notification::make()
                            ->success()
                            ->title('Campagne terminée')
                            ->send();
                    }),

                Action::make('duplicate')
                    ->label('Dupliquer')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newCampaign = $record->replicate();
                        $newCampaign->nom = $record->nom.' (Copie)';
                        $newCampaign->statut = 'planifiee';
                        $newCampaign->date_debut = null;
                        $newCampaign->date_fin = null;
                        $newCampaign->save();

                        Notification::make()
                            ->success()
                            ->title('Campagne dupliquée')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les campagnes sélectionnées')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_activate')
                        ->label('Activer')
                        ->icon('heroicon-m-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->lancer()),
                    Action::make('bulk_terminate')
                        ->label('Terminer')
                        ->icon('heroicon-m-check-circle')
                        ->color('gray')
                        ->action(fn ($records) => $records->each->terminer()),
                ]),
            ])
            ->emptyStateHeading('Aucune campagne marketing')
            ->emptyStateDescription('Créez votre première campagne pour promouvoir vos produits.')
            ->emptyStateIcon('heroicon-o-megaphone')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
