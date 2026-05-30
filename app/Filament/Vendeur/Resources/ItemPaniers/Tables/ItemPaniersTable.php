<?php

namespace App\Filament\Vendeur\Resources\ItemPaniers\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ItemPaniersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('panier.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('produit.nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('variante.nom')
                    ->label('Variante')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state, $record) => $record->variante ? $record->variante->nom.': '.$record->variante->valeur : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('quantite')
                    ->label('Qté')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('prix_unitaire')
                    ->label('Prix unitaire')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('prix_total')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('added_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('panier_id')
                    ->label('Panier')
                    ->relationship('panier', 'id')
                    ->searchable(),

                SelectFilter::make('produit_id')
                    ->label('Produit')
                    ->relationship('produit', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_product')
                    ->label('Voir produit')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.vendeur.products.resources.produits.edit', $record->produit_id))
                    ->color('gray'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun article dans les paniers')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
