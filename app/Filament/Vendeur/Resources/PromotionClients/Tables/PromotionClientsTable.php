<?php

namespace App\Filament\Vendeur\Resources\PromotionClients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PromotionClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('promotion.code')
                    ->label('Promotion')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable(),

                TextColumn::make('client.full_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->client?->email)
                    ->toggleable(),

                TextColumn::make('utilisations')
                    ->label('Utilisations')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($record->utilisations_max && $state >= $record->utilisations_max) {
                            return 'danger';
                        }

                        return $state > 0 ? 'success' : 'gray';
                    })
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->utilisations_max) {
                            return $state.' / '.$record->utilisations_max;
                        }

                        return $state;
                    })
                    ->toggleable(),

                TextColumn::make('utilisations_restantes')
                    ->label('Restantes')
                    ->getStateUsing(fn ($record) => $record->utilisations_max ? max(0, $record->utilisations_max - $record->utilisations) : '∞')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => is_numeric($state) && $state <= 3 ? 'warning' : 'success')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('taux_utilisation')
                    ->label('Taux')
                    ->getStateUsing(fn ($record) => $record->taux_utilisation.'%')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('premiere_utilisation')
                    ->label('Première utilisation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('derniere_utilisation')
                    ->label('Dernière utilisation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'code')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('est_actif')
                    ->label('Actif')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),

                TernaryFilter::make('a_ete_utilise')
                    ->label('Utilisé')
                    ->placeholder('Tous')
                    ->trueLabel('Utilisés')
                    ->falseLabel('Jamais utilisés')
                    ->queries(
                        true: fn ($query) => $query->where('utilisations', '>', 0),
                        false: fn ($query) => $query->where('utilisations', 0),
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->est_actif ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => $record->est_actif ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn ($record) => $record->est_actif ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['est_actif' => ! $record->est_actif])),

                Action::make('reset_utilisations')
                    ->label('Réinitialiser')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->resetUtilisations()),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_activate')
                        ->label('Activer')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['est_actif' => true])),
                    Action::make('bulk_deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['est_actif' => false])),
                ]),
            ])
            ->emptyStateHeading('Aucune association promotion-client')
            ->emptyStateIcon('heroicon-o-user-group')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
