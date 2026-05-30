<?php

namespace App\Filament\Vendeur\Resources\Promotions\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('nom')
                    ->label('Promotion')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->description ? str()->limit($record->description, 20) : null)
                    ->toggleable(),

                // Image principale
                SpatieMediaLibraryImageColumn::make('banner')
                    ->label('Image')
                    ->square()
                    ->collection('banner')
                    ->disk('public')
                    ->visibility('public')
                    ->imageHeight(40)
                    ->imageWidth(50)
                    ->defaultImageUrl(fn ($record) => 'https://placehold.co/400x400?text='.urlencode($record->nom))
                    ->toggleable(),

                TextColumn::make('code')
                    ->label('Code promo')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pourcentage' => 'primary',
                        'montant_fixe' => 'success',
                        'livraison_offerte' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pourcentage' => '% Réduction',
                        'montant_fixe' => '€ Réduction',
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

                TextColumn::make('coupons')
                    ->label('Coupons')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                // TextColumn::make('coupons_count')
                //     ->label('Coupons')
                //     ->counts('coupons')
                //     ->badge()
                //     ->color('info')
                //     ->sortable()
                //     ->toggleable(),

                TextColumn::make('utilisation_courante')
                    ->label('Utilisations')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state, $record) => $record->utilisation_max && $state >= $record->utilisation_max ? 'danger' : 'success')
                    ->toggleable(),

                TextColumn::make('date_debut')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_fin')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('est_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('cumulable')
                    ->label('Cumulable')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
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
                        'pourcentage' => 'Pourcentage',
                        'montant_fixe' => 'Montant fixe',
                        'livraison_offerte' => 'Livraison offerte',
                    ])
                    ->multiple(),

                TernaryFilter::make('est_active')
                    ->label('Promotion active'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('toggle_active')
                    ->label(fn ($record) => $record->est_active ? 'Désactiver' : 'Activer')
                    ->icon(fn ($record) => $record->est_active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn ($record) => $record->est_active ? 'danger' : 'success')
                    ->action(function ($record) {
                        $record->est_active = ! $record->est_active;
                        $record->save();

                        Notification::make()
                            ->success()
                            ->title($record->est_active ? 'Promotion activée' : 'Promotion désactivée')
                            ->send();
                    })
                    ->requiresConfirmation(),

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
                        ->action(fn ($records) => $records->each->update(['est_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Action::make('bulk_deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['est_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('Aucune promotion')
            ->emptyStateDescription('Créez des promotions pour augmenter vos ventes.')
            ->emptyStateIcon('heroicon-o-ticket')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession();
    }
}
