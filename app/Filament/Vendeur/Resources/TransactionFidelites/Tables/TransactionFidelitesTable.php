<?php

namespace App\Filament\Vendeur\Resources\TransactionFidelites\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TransactionFidelitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('compteFidelite.client.full_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->compteFidelite?->client?->email)
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'gain' => 'success',
                        'utilisation' => 'warning',
                        'expiration' => 'danger',
                        'ajustement' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'gain' => 'heroicon-o-plus-circle',
                        'utilisation' => 'heroicon-o-minus-circle',
                        'expiration' => 'heroicon-o-clock',
                        'ajustement' => 'heroicon-o-cog-6-tooth',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'gain' => 'Gain',
                        'utilisation' => 'Utilisation',
                        'expiration' => 'Expiration',
                        'ajustement' => 'Ajustement',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => $state < 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '').number_format($state, 0, ',', ' ').' pts')
                    ->toggleable(),

                TextColumn::make('points_absolus')
                    ->label('Qté')
                    ->getStateUsing(fn ($record) => abs($record->points))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valeur_euro')
                    ->label('Valeur (€)')
                    ->getStateUsing(fn ($record) => $record->valeur_euro)
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('raison')
                    ->label('Raison')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('date_transaction')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                IconColumn::make('est_gain')
                    ->label('Type')
                    ->boolean()
                    ->trueIcon('heroicon-o-plus-circle')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->points > 0)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_transaction', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'gain' => 'Gain',
                        'utilisation' => 'Utilisation',
                        'expiration' => 'Expiration',
                        'ajustement' => 'Ajustement',
                    ])
                    ->multiple(),

                SelectFilter::make('compte_fidelite_id')
                    ->label('Compte fidélité')
                    ->relationship('compteFidelite', 'id')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('points')
                    ->label('Sens')
                    ->placeholder('Tous')
                    ->trueLabel('Points positifs (gains)')
                    ->falseLabel('Points négatifs (dépenses)')
                    ->queries(
                        true: fn ($query) => $query->where('points', '>', 0),
                        false: fn ($query) => $query->where('points', '<', 0),
                    ),

                Filter::make('date_transaction')
                    ->label('Période')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Du'),
                        DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date_transaction', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('date_transaction', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->points > 0 && $record->date_transaction->diffInDays(now()) <= 7)
                    ->action(function ($record) {
                        // Créer une transaction d'annulation
                        $record->compteFidelite->ajouterPoints(-$record->points, "Annulation: {$record->raison}");
                        Notification::make()
                            ->success()
                            ->title('Transaction annulée')
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les transactions sélectionnées')
                        ->modalDescription('Cette action est irréversible.')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                ]),
            ])
            ->emptyStateHeading('Aucune transaction')
            ->emptyStateDescription('Les transactions de fidélité apparaîtront ici.')
            ->emptyStateIcon('heroicon-o-currency-euro')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('date_transaction', 'desc');
    }
}
