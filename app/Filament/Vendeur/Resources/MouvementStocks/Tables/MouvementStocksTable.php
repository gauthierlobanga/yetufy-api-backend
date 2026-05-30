<?php

namespace App\Filament\Vendeur\Resources\MouvementStocks\Tables;

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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;

class MouvementStocksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('produit.nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->produit?->sku)
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entree' => 'success',
                        'sortie' => 'danger',
                        'ajustement' => 'warning',
                        'transfert' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entree' => '📥 Entrée',
                        'sortie' => '📤 Sortie',
                        'ajustement' => '⚙️ Ajustement',
                        'transfert' => '🔄 Transfert',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantite')
                    ->label('Quantité')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '').Number::format($state))
                    ->toggleable(),

                TextColumn::make('valeur_totale')
                    ->label('Valeur totale')
                    ->getStateUsing(fn ($record) => ($record->produit?->prix_ttc ?? 0) * abs($record->quantite))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('entrepot.nom')
                    ->label('Entrepôt')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_mouvement')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                IconColumn::make('est_annule')
                    ->label('Annulé')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->getStateUsing(fn ($record) => $record->deleted_at !== null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_mouvement', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'entree' => 'Entrée',
                        'sortie' => 'Sortie',
                        'ajustement' => 'Ajustement',
                        'transfert' => 'Transfert',
                    ])
                    ->multiple(),

                SelectFilter::make('produit_id')
                    ->label('Produit')
                    ->relationship('produit', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('entrepot_id')
                    ->label('Entrepôt')
                    ->relationship('entrepot', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('quantite')
                    ->label('Type de quantité')
                    ->placeholder('Tous')
                    ->trueLabel('Sorties (négatives)')
                    ->falseLabel('Entrées (positives)')
                    ->queries(
                        true: fn ($query) => $query->where('quantite', '<', 0),
                        false: fn ($query) => $query->where('quantite', '>', 0),
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->deleted_at === null)
                    ->action(function ($record) {
                        $record->delete();
                        Notification::make()
                            ->success()
                            ->title('Mouvement annulé')
                            ->send();
                    }),

                Action::make('restaurer')
                    ->label('Restaurer')
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn ($record) => $record->deleted_at !== null)
                    ->action(function ($record) {
                        $record->restore();
                        Notification::make()
                            ->success()
                            ->title('Mouvement restauré')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les mouvements sélectionnés')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun mouvement de stock')
            ->emptyStateDescription('Les mouvements de stock apparaîtront ici lors des entrées et sorties.')
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100, 250])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('date_mouvement', 'desc');
    }
}
