<?php

namespace App\Filament\Vendeur\Resources\Taxes\Tables;

use App\Models\Taxe;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TaxesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nom')
                    ->label('Taxe')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('taux')
                    ->label('Taux')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('pays')
                    ->label('Pays')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('region')
                    ->label('Région')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_defaut')
                    ->label('Par défaut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('produits_count')
                    ->label('Produits')
                    ->counts('produits')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('taux', 'desc')
            ->filters([
                SelectFilter::make('pays')
                    ->label('Pays')
                    ->options([
                        'France' => 'France',
                        'Belgique' => 'Belgique',
                        'Suisse' => 'Suisse',
                        'Canada' => 'Canada',
                        'États-Unis' => 'États-Unis',
                        'Royaume-Uni' => 'Royaume-Uni',
                    ])
                    ->multiple(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('set_default')
                    ->label('Définir par défaut')
                    ->icon('heroicon-m-star')
                    ->color('warning')
                    ->visible(fn ($record) => ! $record->est_defaut)
                    ->action(function ($record) {
                        Taxe::query()->update(['est_defaut' => false]);
                        $record->update(['est_defaut' => true]);
                        Notification::make()
                            ->success()
                            ->title('Taxe définie par défaut')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune taxe')
            ->emptyStateDescription('Créez des taxes pour gérer la TVA par pays/région.')
            ->emptyStateIcon('heroicon-o-calculator')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('taux', 'desc');
    }
}
