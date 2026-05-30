<?php

namespace App\Filament\Vendeur\Resources\ProgrammeFidelites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProgrammeFidelitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nom')
                    ->label('Programme')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'points' => 'primary',
                        'paliers' => 'warning',
                        'cashback' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'points' => 'Points',
                        'paliers' => 'Paliers',
                        'cashback' => 'Cashback',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->counts('comptes')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('points_distribues')
                    ->label('Points distribués')
                    ->getStateUsing(fn ($record) => $record->points_distribues)
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun programme de fidélité')
            ->emptyStateDescription('Créez un programme pour fidéliser vos clients.')
            ->emptyStateIcon('heroicon-o-star')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
