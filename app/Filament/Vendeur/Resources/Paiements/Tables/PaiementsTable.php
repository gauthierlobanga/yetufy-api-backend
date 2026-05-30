<?php

namespace App\Filament\Vendeur\Resources\Paiements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PaiementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commande.id')
                    ->searchable(),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->searchable(),
                TextColumn::make('mode')
                    ->searchable(),
                TextColumn::make('carte_brand')
                    ->searchable(),
                TextColumn::make('carte_last4')
                    ->searchable(),
                TextColumn::make('montant')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('devise')
                    ->searchable(),
                TextColumn::make('statut')
                    ->searchable(),
                TextColumn::make('date_paiement')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('date_remboursement')
                    ->dateTime()
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
