<?php

namespace App\Filament\Vendeur\Resources\WishlistItems\Tables;

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
use Filament\Tables\Table;

class WishlistItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('wishlist.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('wishlist.nom')
                    ->label('Liste')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('produit.nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->produit?->reference)
                    ->toggleable(),

                TextColumn::make('quantite')
                    ->label('Qté')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('produit.prix_ttc')
                    ->label('Prix unitaire')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('prix_total')
                    ->label('Total estimé')
                    ->getStateUsing(fn ($record) => $record->produit?->prix_ttc * $record->quantite)
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('produit.est_en_stock')
                    ->label('Disponible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->produit?->stock_disponible > 0)
                    ->toggleable(),

                TextColumn::make('note')
                    ->label('Note')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('added_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('wishlist_id')
                    ->label('Liste de souhaits')
                    ->relationship('wishlist', 'nom')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('produit_id')
                    ->label('Produit')
                    ->relationship('produit', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

            ])
            ->recordActions([
                Action::make('view_product')
                    ->label('Voir produit')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.vendeur.products.resources.produits.edit', $record->produit_id))
                    ->color('gray'),

                Action::make('add_to_cart')
                    ->label('Ajouter au panier')
                    ->icon('heroicon-m-shopping-cart')
                    ->color('success')
                    ->action(function ($record) {
                        // Logique d'ajout au panier
                        Notification::make()
                            ->success()
                            ->title('Produit ajouté au panier')
                            ->body($record->quantite.' x '.$record->produit->nom)
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
            ->emptyStateHeading('Aucun article dans les listes de souhaits')
            ->emptyStateDescription('Les articles ajoutés aux listes de souhaits apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-heart')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
