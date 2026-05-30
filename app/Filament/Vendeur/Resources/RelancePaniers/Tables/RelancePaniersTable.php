<?php

namespace App\Filament\Vendeur\Resources\RelancePaniers\Tables;

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

class RelancePaniersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('abandonPanier.panier.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('canal')
                    ->label('Canal')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'primary',
                        'sms' => 'success',
                        'push' => 'warning',
                        'notification' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'envoye' => 'primary',
                        'ouvert' => 'warning',
                        'clique' => 'info',
                        'converti' => 'success',
                        'echec' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

                TextColumn::make('taux_conversion')
                    ->label('Taux conversion')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => ($state ?? 0) > 50 ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('a_conduit_achat')
                    ->label('Achat')
                    ->boolean()
                    ->trueIcon('heroicon-o-shopping-cart')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                TextColumn::make('delai_ouverture')
                    ->label('Délai ouverture')
                    ->getStateUsing(fn ($record) => $record->delai_ouverture ? $record->delai_ouverture.' min' : '-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('envoye_at')
                    ->label("Date d'envoi")
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('ouvert_at')
                    ->label("Date d'ouverture")
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('canal')
                    ->label('Canal')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'push' => 'Notification push',
                        'notification' => 'Notification',
                    ])
                    ->preload()
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'envoye' => 'Envoyé',
                        'ouvert' => 'Ouvert',
                        'clique' => 'Cliqué',
                        'converti' => 'Converti',
                        'echec' => 'Échec',
                    ])
                    ->preload()
                    ->searchable()
                    ->multiple(),

                TernaryFilter::make('a_conduit_achat')
                    ->preload()
                    ->searchable()
                    ->label('A conduit à un achat'),

                TrashedFilter::make()
                    ->preload()
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('marquer_ouvert')
                    ->label('Marquer ouvert')
                    ->icon('heroicon-m-eye')
                    ->color('warning')
                    ->visible(fn ($record) => $record->statut === 'envoye')
                    ->action(function ($record) {
                        $record->marquerOuvert();
                        Notification::make()
                            ->success()
                            ->title('Relance marquée comme ouverte')
                            ->send();
                    }),

                Action::make('marquer_clique')
                    ->label('Marquer cliqué')
                    ->icon('heroicon-m-cursor-arrow-rays')
                    ->color('info')
                    ->visible(fn ($record) => $record->statut === 'ouvert')
                    ->action(function ($record) {
                        $record->marquerClique();
                        Notification::make()
                            ->success()
                            ->title('Relance marquée comme cliquée')
                            ->send();
                    }),

                Action::make('marquer_converti')
                    ->label('Marquer converti')
                    ->icon('heroicon-m-shopping-cart')
                    ->color('success')
                    ->visible(fn ($record) => $record->statut === 'clique')
                    ->action(function ($record) {
                        $record->marquerConverti();
                        Notification::make()
                            ->success()
                            ->title('Relance marquée comme convertie')
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
            ->emptyStateHeading('Aucune relance')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
