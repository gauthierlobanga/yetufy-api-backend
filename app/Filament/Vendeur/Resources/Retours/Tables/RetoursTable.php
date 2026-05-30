<?php

namespace App\Filament\Vendeur\Resources\Retours\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RetoursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('commande.numero_commande')
                    ->label('Commande')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('commande.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('motif_label')
                    ->label('Motif')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'warning',
                        'accepte' => 'success',
                        'refuse' => 'danger',
                        'en_cours' => 'info',
                        'termine' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'accepte' => 'Accepté',
                        'refuse' => 'Refusé',
                        'en_cours' => 'En cours',
                        'termine' => 'Terminé',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'remboursement' => 'success',
                        'avoir' => 'info',
                        'echange' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'remboursement' => 'Remboursement',
                        'avoir' => 'Avoir',
                        'echange' => 'Échange',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('montant_total')
                    ->label('Montant')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('date_demande')
                    ->label('Demandé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('date_traitement')
                    ->label('Traité le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'accepte' => 'Accepté',
                        'refuse' => 'Refusé',
                        'en_cours' => 'En cours',
                        'termine' => 'Terminé',
                    ])
                    ->multiple(),

                SelectFilter::make('action')
                    ->label('Action')
                    ->options([
                        'remboursement' => 'Remboursement',
                        'avoir' => 'Avoir',
                        'echange' => 'Échange',
                    ])
                    ->multiple(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('accepter')
                    ->label('Accepter')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->action(function ($record) {
                        $record->accepter();
                        Notification::make()
                            ->success()
                            ->title('Retour accepté')
                            ->send();
                    }),

                Action::make('refuser')
                    ->label('Refuser')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->statut === 'en_attente')
                    ->action(function ($record) {
                        $record->refuser('Refusé par l\'administrateur');
                        Notification::make()
                            ->success()
                            ->title('Retour refusé')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_accept')
                        ->label('Accepter')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->accepter()),
                ]),
            ])
            ->emptyStateHeading('Aucun retour')
            ->emptyStateIcon('heroicon-o-arrow-uturn-left')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
