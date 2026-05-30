<?php

namespace App\Filament\Vendeur\Resources\PromotionPaniers\Tables;

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

class PromotionPaniersTable
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

                TextColumn::make('panier.client.full_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('montant_applique')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(),

                TextColumn::make('taux_reduction')
                    ->label('Taux')
                    ->getStateUsing(fn ($record) => $record->taux_reduction ? $record->taux_reduction.'%' : '-')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('code_saisi')
                    ->label('Code saisi')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_manuelle')
                    ->label('Type')
                    ->boolean()
                    ->trueIcon('heroicon-o-user')
                    ->falseIcon('heroicon-o-cog-6-tooth')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->toggleable(),

                TextColumn::make('applied_at')
                    ->label('Appliquée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('applied_at', 'desc')
            ->filters([
                SelectFilter::make('promotion_id')
                    ->label('Promotion')
                    ->relationship('promotion', 'code')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('est_manuelle')
                    ->label('Type d\'application')
                    ->placeholder('Tous')
                    ->trueLabel('Manuelles')
                    ->falseLabel('Automatiques'),

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
            ->emptyStateHeading('Aucune application de promotion')
            ->emptyStateIcon('heroicon-o-ticket')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('applied_at', 'desc');
    }
}
