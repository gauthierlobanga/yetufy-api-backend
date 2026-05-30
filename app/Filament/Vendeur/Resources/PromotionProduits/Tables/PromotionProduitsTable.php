<?php

namespace App\Filament\Vendeur\Resources\PromotionProduits\Tables;

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

class PromotionProduitsTable
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

                TextColumn::make('produit.nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->produit?->reference)
                    ->toggleable(),

                TextColumn::make('valeur_specifique')
                    ->label('Valeur spécifique')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $state) {
                            return 'Par défaut';
                        }

                        return $record->promotion?->type === 'pourcentage'
                            ? $state.'%'
                            : number_format($state, 2).' €';
                    })
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => $state ? 'warning' : 'gray')
                    ->toggleable(),

                TextColumn::make('quantite_minimale')
                    ->label('Qté min')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('quantite_maximale')
                    ->label('Qté max')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
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

                SelectFilter::make('produit_id')
                    ->label('Produit')
                    ->relationship('produit', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('est_actif')
                    ->label('Actif')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->est_actif ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => $record->est_actif ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn ($record) => $record->est_actif ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['est_actif' => ! $record->est_actif])),

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
            ->emptyStateHeading('Aucune association promotion-produit')
            ->emptyStateIcon('heroicon-o-ticket')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
