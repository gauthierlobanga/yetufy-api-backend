<?php

namespace App\Filament\Vendeur\Resources\Tags\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Spatie\Tags\Tag;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ColorColumn::make('color')
                    ->label('')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Tag')
                    ->searchable(query: function ($query, $search) {
                        return $query->where('name->fr', 'like', "%{$search}%")
                            ->orWhere('name->en', 'like', "%{$search}%");
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('name->fr', $direction);
                    })
                    ->formatStateUsing(fn ($state) => $state['fr'] ?? $state['en'] ?? '')
                    ->weight('medium')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => $state ?: 'Général')
                    ->toggleable(),

                TextColumn::make('order_column')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_active')
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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->defaultSort('order_column')
            ->reorderable('order_column')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(function () {
                        return Tag::distinct()
                            ->pluck('type', 'type')
                            ->filter()
                            ->mapWithKeys(fn ($type) => [$type => $type ?: 'Général'])
                            ->toArray();
                    }),

                TernaryFilter::make('is_active')
                    ->label('Statut actif')
                    ->placeholder('Tous')
                    ->trueLabel('Tags actifs')
                    ->falseLabel('Tags inactifs'),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),

                Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ?? true ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => ($record->is_active ?? true) ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn ($record) => ($record->is_active ?? true) ? 'danger' : 'success')
                    ->action(function ($record) {
                        $record->is_active = ! ($record->is_active ?? true);
                        $record->save();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les tags sélectionnés')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                ]),
            ])
            ->emptyStateHeading('Aucun tag')
            ->emptyStateDescription('Créez des tags pour organiser vos produits et articles.')
            ->emptyStateIcon('heroicon-o-tag')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('order_column');
    }
}
