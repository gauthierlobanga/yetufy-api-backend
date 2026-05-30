<?php

namespace App\Filament\Vendeur\Resources\LigneCommandeAchats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LigneCommandeAchatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('commandeAchat.numero_commande')
                    ->searchable(),
                TextColumn::make('produit.nom')
                    ->searchable(),
                TextColumn::make('quantite')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantite_recue')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prix_unitaire_ht')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_ht')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tva')
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
