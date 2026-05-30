<?php

namespace App\Filament\Resources\Plans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->slug)
                    ->tooltip(fn ($record) => $record->highlight),

                // Remplacement de BadgeColumn par TextColumn + badge()
                TextColumn::make('price')
                    ->label('Prix')
                    ->formatStateUsing(fn ($state, $record) => $record->price > 0
                        ? number_format($state, 2).' '.($record->currency ?? 'CDF')
                        : 'Gratuit')
                    ->badge()
                    ->color(fn ($record) => $record->price > 0 ? 'amber' : 'success')
                    ->sortable(),

                TextColumn::make('interval')
                    ->label('Intervalle')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'day' => 'Jour', 'week' => 'Semaine', 'month' => 'Mois',
                        'quarter' => 'Trimestre', 'year' => 'Année', 'lifetime' => 'À vie',
                        default => $state
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('trial_days')
                    ->label('Essai (j)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false),

                IconColumn::make('is_featured')
                    ->label('Vedette')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_recommended')
                    ->label('Recommandé')
                    ->boolean()
                    ->toggleable(),

                // Colonne "badge" corrigée : TextColumn + badge()
                TextColumn::make('badge')
                    ->label('Badge')
                    ->icon(fn ($record) => $record->badge ? 'heroicon-o-sparkles' : null)
                    ->badge()
                    ->color(fn ($record) => $record->badge_color ?? 'gray')
                    ->toggleable(),

                TextColumn::make('button_text')
                    ->label('Texte bouton')
                    ->searchable()
                    ->placeholder('Sélectionner')
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Actif'),

                TernaryFilter::make('is_featured')
                    ->label('En vedette'),

                TernaryFilter::make('is_recommended')
                    ->label('Recommandé'),

                SelectFilter::make('interval')
                    ->label('Intervalle')
                    ->options([
                        'day' => 'Jour',
                        'week' => 'Semaine',
                        'month' => 'Mois',
                        'quarter' => 'Trimestre',
                        'year' => 'Année',
                        'lifetime' => 'À vie',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
