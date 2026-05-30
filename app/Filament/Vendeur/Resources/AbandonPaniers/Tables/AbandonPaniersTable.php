<?php

namespace App\Filament\Vendeur\Resources\AbandonPaniers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AbandonPaniersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('panier.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->panier?->client?->email)
                    ->toggleable(),

                TextColumn::make('valeur_panier')
                    ->label('Valeur panier')
                    ->getStateUsing(fn ($record) => $record->panier?->total_general ?? 0)
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => $state > 200 ? 'success' : ($state > 100 ? 'warning' : 'gray'))
                    ->toggleable(),

                TextColumn::make('etape_abandon')
                    ->label('Étape')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'panier' => 'gray',
                        'identification' => 'warning',
                        'livraison' => 'info',
                        'paiement' => 'danger',
                        'confirmation' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'panier' => 'Panier',
                        'identification' => 'Identification',
                        'livraison' => 'Livraison',
                        'paiement' => 'Paiement',
                        'confirmation' => 'Confirmation',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('raison')
                    ->label('Raison')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nombre_relances')
                    ->label('Relances')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 3 ? 'danger' : ($state > 0 ? 'warning' : 'gray'))
                    ->toggleable(),

                IconColumn::make('recupere')
                    ->label('Récupéré')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('temps_abandon')
                    ->label('Temps depuis abandon')
                    ->getStateUsing(fn ($record) => $record->created_at?->diffForHumans())
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('score_priorite')
                    ->label('Priorité')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state >= 7 ? 'danger' : ($state >= 4 ? 'warning' : 'success'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Abandonné le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('etape_abandon')
                    ->label('Étape')
                    ->options([
                        'panier' => 'Panier',
                        'identification' => 'Identification',
                        'livraison' => 'Livraison',
                        'paiement' => 'Paiement',
                        'confirmation' => 'Confirmation',
                    ])
                    ->multiple(),

                SelectFilter::make('raison')
                    ->label('Raison')
                    ->options([
                        'prix_trop_eleve' => 'Prix trop élevé',
                        'frais_livraison' => 'Frais de livraison',
                        'creation_compte' => 'Création de compte',
                        'probleme_technique' => 'Problème technique',
                        'comparaison_prix' => 'Comparaison des prix',
                        'autre' => 'Autre raison',
                    ])
                    ->multiple(),

                TernaryFilter::make('recupere')
                    ->label('Récupéré')
                    ->placeholder('Tous')
                    ->trueLabel('Récupérés')
                    ->falseLabel('Non récupérés'),

                TernaryFilter::make('has_relances')
                    ->label('Relancé')
                    ->placeholder('Tous')
                    ->trueLabel('Relancés')
                    ->falseLabel('Jamais relancés')
                    ->queries(
                        true: fn ($query) => $query->where('nombre_relances', '>', 0),
                        false: fn ($query) => $query->where('nombre_relances', 0),
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('relancer')
                    ->label('Relancer')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('primary')
                    ->visible(fn ($record) => ! $record->recupere)
                    ->action(function ($record) {
                        // Logique de relance
                        $record->enregistrerRelance('email', ['template' => 'abandon_panier']);
                        Notification::make()
                            ->success()
                            ->title('Relance envoyée')
                            ->send();
                    }),

                Action::make('marquer_recupere')
                    ->label('Marquer récupéré')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->recupere)
                    ->action(function ($record) {
                        $record->marquerRecupere();
                        Notification::make()
                            ->success()
                            ->title('Panier marqué comme récupéré')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_relancer')
                        ->label('Relancer sélectionnés')
                        ->icon('heroicon-m-chat-bubble-left-right')
                        ->color('primary')
                        ->action(fn ($records) => $records->each->enregistrerRelance('email')),
                    Action::make('bulk_marquer_recupere')
                        ->label('Marquer récupérés')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->marquerRecupere()),
                ]),
            ])
            ->emptyStateHeading('Aucun panier abandonné')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
