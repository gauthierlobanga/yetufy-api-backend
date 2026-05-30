<?php

namespace App\Filament\Vendeur\Resources\VarianteProduits\Tables;

use App\Models\VarianteProduit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class VarianteProduitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Informations de la variante
                TextColumn::make('produit.nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->produit->reference ?? '')
                    ->toggleable(),

                TextColumn::make('nom')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('valeur')
                    ->label('Valeur')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('nom_complet')
                    ->label('Variante complète')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // SKU
                TextColumn::make('sku_variante')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('SKU copié')
                    ->icon('heroicon-m-qr-code')
                    ->toggleable(),

                // Prix
                TextColumn::make('supplement_prix')
                    ->label('Supplément')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->toggleable(),

                TextColumn::make('prix_actuel')
                    ->label('Prix final')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->toggleable(),

                // Stock
                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success'))
                    ->formatStateUsing(fn ($state) => Number::format($state))
                    ->toggleable(),

                IconColumn::make('en_stock')
                    ->label('Disponible')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->stock > 0)
                    ->toggleable(),

                // Statistiques (ventes)
                TextColumn::make('total_vendus')
                    ->label('Vendus')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->default(0),

                // Dates
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),

                TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('produit_id')
                    ->label('Produit')
                    ->relationship('produit', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('stock')
                    ->label('État du stock')
                    ->placeholder('Toutes les variantes')
                    ->trueLabel('En stock')
                    ->falseLabel('Rupture')
                    ->queries(
                        true: fn (Builder $query) => $query->where('stock', '>', 0),
                        false: fn (Builder $query) => $query->where('stock', '<=', 0),
                    ),

                SelectFilter::make('nom')
                    ->label('Type de variante')
                    ->options(function () {
                        return VarianteProduit::distinct()
                            ->pluck('nom', 'nom')
                            ->toArray();
                    })
                    ->multiple(),

                TrashedFilter::make()
                    ->label('Corbeille'),
            ])
            ->recordActions([
                Action::make('view_product')
                    ->label('Voir le produit')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn ($record) => route('filament.vendeur.products.resources.produits.edit', $record->produit_id))
                    ->color('gray')
                    ->button(),

                EditAction::make()
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->button(),

                Action::make('adjust_stock')
                    ->label('Ajuster le stock')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->color('success')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Quantité à ajouter/retirer')
                            ->numeric()
                            ->required()
                            ->step(1)
                            ->helperText('Nombre positif pour ajouter, négatif pour retirer'),
                        Textarea::make('reason')
                            ->label('Raison')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $newStock = $record->stock + $data['quantity'];
                        if ($newStock < 0) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur')
                                ->body('Le stock ne peut pas devenir négatif.')
                                ->send();

                            return;
                        }
                        $record->stock = $newStock;
                        $record->save();

                        Notification::make()
                            ->success()
                            ->title('Stock mis à jour')
                            ->body("Nouveau stock: {$record->stock}")
                            ->send();
                    })->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les variantes sélectionnées')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('increase_stock')
                        ->label('Augmenter le stock')
                        ->icon('heroicon-m-plus-circle')
                        ->color('success')
                        ->form([
                            TextInput::make('quantity')
                                ->label('Quantité à ajouter')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(1),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->stock += $data['quantity'];
                                $record->save();
                            }
                        }),
                    Action::make('decrease_stock')
                        ->label('Diminuer le stock')
                        ->icon('heroicon-m-minus-circle')
                        ->color('warning')
                        ->form([
                            TextInput::make('quantity')
                                ->label('Quantité à retirer')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(1),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $newStock = $record->stock - $data['quantity'];
                                if ($newStock >= 0) {
                                    $record->stock = $newStock;
                                    $record->save();
                                }
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Aucune variante')
            ->emptyStateDescription('Créez des variantes pour personnaliser vos produits (taille, couleur, etc.)')
            ->emptyStateIcon('heroicon-o-bars-3')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
