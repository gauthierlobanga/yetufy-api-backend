<?php

namespace App\Filament\Vendeur\Resources\LigneCommandes\Tables;

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

class LigneCommandesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Commande
                TextColumn::make('commande.numero_commande')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable(),

                // Produit
                TextColumn::make('nom_produit')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->produit?->reference)
                    ->toggleable(),

                // Variante
                TextColumn::make('variante.nom')
                    ->label('Variante')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state, $record) => $record->variante ? $record->variante->nom.': '.$record->variante->valeur : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Quantité
                TextColumn::make('quantite')
                    ->label('Qté')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                // Prix unitaire
                TextColumn::make('prix_unitaire')
                    ->label('Prix unitaire')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                // Taxe
                TextColumn::make('taxe')
                    ->label('TVA unitaire')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Remise
                TextColumn::make('remise')
                    ->label('Remise')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Prix total
                TextColumn::make('prix_total')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->toggleable(),

                // Sous-total
                TextColumn::make('sous_total')
                    ->label('Sous-total HT')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Total TTC calculé
                TextColumn::make('total_ttc')
                    ->label('Total avec taxes')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Dates
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('commande_id')
                    ->label('Commande')
                    ->relationship('commande', 'numero_commande')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('produit_id')
                    ->label('Produit')
                    ->relationship('produit', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TrashedFilter::make()
                    ->label('Corbeille'),
            ])
            ->recordActions([
                Action::make('view_product')
                    ->label('Voir produit')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) => route('filament.vendeur.products.resources.produits.index', $record->produit_id))
                    ->color('gray'),

                Action::make('view_commande')
                    ->label('Voir commande')
                    ->icon('heroicon-m-shopping-cart')
                    ->url(fn ($record) => route('filament.vendeur.commandes.resources.commandes.index', $record->commande_id))
                    ->color('info'),

                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les lignes sélectionnées')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune ligne de commande')
            ->emptyStateDescription('Les lignes de commande apparaîtront ici une fois les commandes créées.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
