<?php

namespace App\Filament\Vendeur\Resources\ReglePaniers\Tables;

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

class ReglePaniersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('panier.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'remise_pourcentage' => 'primary',
                        'remise_montant' => 'success',
                        'livraison_offerte' => 'warning',
                        'produit_offert' => 'info',
                        'code_promo' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'remise_pourcentage' => '% Remise',
                        'remise_montant' => '€ Remise',
                        'livraison_offerte' => 'Livraison offerte',
                        'produit_offert' => 'Produit offert',
                        'code_promo' => 'Code promo',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valeur')
                    ->label('Valeur')
                    ->formatStateUsing(fn ($record) => $record->type === 'remise_pourcentage'
                            ? $record->valeur.'%'
                            : ($record->type === 'remise_montant' ? number_format($record->valeur, 2).' €' : '-')
                    )
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('libelle')
                    ->label('Libellé')
                    ->getStateUsing(fn ($record) => $record->libelle)
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('appliquee')
                    ->label('Appliquée')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('montant_reduction')
                    ->label('Réduction')
                    ->money('EUR')
                    ->getStateUsing(fn ($record) => $record->montant_reduction)
                    ->sortable()
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('applied_at')
                    ->label('Appliquée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'remise_pourcentage' => '% Remise',
                        'remise_montant' => '€ Remise',
                        'livraison_offerte' => 'Livraison offerte',
                        'produit_offert' => 'Produit offert',
                        'code_promo' => 'Code promo',
                    ])
                    ->multiple(),

                TernaryFilter::make('appliquee')
                    ->label('Appliquée')
                    ->placeholder('Toutes')
                    ->trueLabel('Appliquées')
                    ->falseLabel('Non appliquées'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('appliquer')
                    ->label('Appliquer')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->visible(fn ($record) => ! $record->appliquee)
                    ->action(function ($record) {
                        $record->appliquer();
                        Notification::make()
                            ->success()
                            ->title('Règle appliquée avec succès')
                            ->send();
                    }),

                Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-m-stop')
                    ->color('danger')
                    ->visible(fn ($record) => $record->appliquee)
                    ->action(function ($record) {
                        $record->annuler();
                        Notification::make()
                            ->success()
                            ->title('Règle annulée')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_appliquer')
                        ->label('Appliquer sélectionnées')
                        ->icon('heroicon-m-play')
                        ->color('success')
                        ->action(fn ($records) => $records->each->appliquer()),
                ]),
            ])
            ->emptyStateHeading('Aucune règle')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
