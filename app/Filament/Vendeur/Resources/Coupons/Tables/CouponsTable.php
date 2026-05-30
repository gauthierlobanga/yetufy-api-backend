<?php

namespace App\Filament\Vendeur\Resources\Coupons\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Code copié')
                    ->toggleable(),

                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pourcentage' => 'primary',
                        'montant_fixe' => 'success',
                        'livraison_offerte' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pourcentage' => 'Pourcentage',
                        'montant_fixe' => 'Montant fixe',
                        'livraison_offerte' => 'Livraison offerte',
                        default => $state,
                    })
                    ->toggleable(),

                TextColumn::make('valeur')
                    ->label('Valeur')
                    ->formatStateUsing(fn ($record) => $record->type === 'pourcentage' ? $record->valeur.'%' : number_format($record->valeur, 2).' €')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('minimum_panier')
                    ->label('Min. panier')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('utilisation_max')
                    ->label('Max utilisations')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_utilise')
                    ->label('Utilisations')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state, $record) => $record->utilisation_max && $state >= $record->utilisation_max ? 'danger' : 'success')
                    ->toggleable(),

                IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('date_debut')
                    ->label('Début')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('free_shipping')
                    ->label('Livraison offerte')
                    ->boolean()
                    ->trueIcon('heroicon-o-truck')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'pourcentage' => 'Pourcentage',
                        'montant_fixe' => 'Montant fixe',
                        'livraison_offerte' => 'Livraison offerte',
                    ]),

                TernaryFilter::make('est_actif')
                    ->label('Coupon actif')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),

                TernaryFilter::make('free_shipping')
                    ->label('Livraison offerte'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->est_actif ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => $record->est_actif ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn ($record) => $record->est_actif ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['est_actif' => ! $record->est_actif])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_activate')
                        ->label('Activer')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['est_actif' => true])),
                    Action::make('bulk_deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['est_actif' => false])),
                ]),
            ])
            ->emptyStateHeading('Aucun coupon')
            ->emptyStateDescription('Créez des codes promotionnels pour fidéliser vos clients.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
