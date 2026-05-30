<?php

namespace App\Filament\Vendeur\Resources\CommandeAchats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommandeAchatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('fournisseur.nom')
                    ->searchable(),
                TextColumn::make('numero_commande')
                    ->searchable(),
                TextColumn::make('date_commande')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_livraison_prevue')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_livraison_reelle')
                    ->date()
                    ->sortable(),
                TextColumn::make('statut')
                    ->searchable(),
                TextColumn::make('sous_total_ht')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('remise')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frais_livraison')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxe')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_ht')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_ttc')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            ]);
    }
}
