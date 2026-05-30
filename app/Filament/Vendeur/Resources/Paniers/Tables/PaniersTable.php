<?php

namespace App\Filament\Vendeur\Resources\Paniers\Tables;

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

class PaniersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->client?->email)
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Staff')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('session_id')
                    ->label('Session')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'abandonne' => 'warning',
                        'converti' => 'info',
                        'expire' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_general')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->toggleable(),

                TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('items_quantity')
                    ->label('Qté')
                    ->getStateUsing(fn ($record) => $record->items->sum('quantite'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_expire')
                    ->label('Expiré')
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->getStateUsing(fn ($record) => $record->expires_at && $record->expires_at->isPast())
                    ->toggleable(),

                TextColumn::make('date_creation')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('date_abandon')
                    ->label('Abandonné le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_conversion')
                    ->label('Converti le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'abandonne' => 'Abandonné',
                        'converti' => 'Converti',
                        'expire' => 'Expiré',
                    ])
                    ->multiple(),

                SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'nom')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('has_items')
                    ->label('Avec articles')
                    ->placeholder('Tous')
                    ->trueLabel('Avec articles')
                    ->falseLabel('Vide')
                    ->queries(
                        true: fn ($query) => $query->has('items'),
                        false: fn ($query) => $query->doesntHave('items'),
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_items')
                    ->label('Voir articles')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.vendeur.paniers.resources.paniers.index', [
                        'tableFilters[panier_id][value]' => $record->id,
                    ]))
                    ->color('info'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun panier')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
