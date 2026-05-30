<?php

namespace App\Filament\Vendeur\Resources\Adresses\Tables;

use App\Enums\AddressType;
use App\Models\Adresse;
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

class AdressesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Remplacer adresse_complete par des colonnes réelles
                TextColumn::make('rue')
                    ->label('Rue')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable(),

                TextColumn::make('code_postal')
                    ->label('Code postal')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ville')
                    ->label('Ville')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('pays')
                    ->label('Pays')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                // Complément d'adresse (optionnel)
                TextColumn::make('complement')
                    ->label('Complément')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Région
                TextColumn::make('region')
                    ->label('Région')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Téléphone
                TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Instructions
                TextColumn::make('instructions')
                    ->label('Instructions')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Type avec les couleurs et icônes de l'Enum
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (AddressType $state): string => $state->getColor())
                    ->icon(fn (AddressType $state) => $state->getIcon())
                    ->formatStateUsing(fn (AddressType $state): string => $state->getLabel())
                    ->tooltip(fn (AddressType $state): string => $state->getDescription() ?? '')
                    ->sortable()
                    ->toggleable(),

                // Par défaut
                IconColumn::make('est_defaut')
                    ->label('Par défaut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                // Entité associée
                TextColumn::make('addressable_type')
                    ->label('Type d\'entité')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('addressable_id')
                    ->label('ID entité')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('type')
                    ->label('Type d\'adresse')
                    ->options(AddressType::class)
                    ->attribute('type'),

                TernaryFilter::make('est_defaut')
                    ->label('Adresse par défaut')
                    ->placeholder('Toutes')
                    ->trueLabel('Adresses par défaut')
                    ->falseLabel('Adresses secondaires'),

                SelectFilter::make('pays')
                    ->label('Pays')
                    ->options(function () {
                        return Adresse::distinct()
                            ->pluck('pays', 'pays')
                            ->filter()
                            ->toArray();
                    })
                    ->multiple(),

                SelectFilter::make('addressable_type')
                    ->label('Type d\'entité')
                    ->options(function () {
                        return Adresse::distinct()
                            ->pluck('addressable_type', 'addressable_type')
                            ->map(fn ($type) => class_basename($type))
                            ->toArray();
                    }),

                TrashedFilter::make()
                    ->label('Corbeille'),
            ])
            ->recordActions([
                Action::make('set_default')
                    ->label('Définir par défaut')
                    ->icon('heroicon-m-star')
                    ->color('warning')
                    ->visible(fn ($record) => ! $record->est_defaut)
                    ->action(function ($record) {
                        $record->definirCommeDefaut();
                        Notification::make()
                            ->success()
                            ->title('Adresse définie par défaut')
                            ->send();
                    }),

                Action::make('unset_default')
                    ->label('Retirer par défaut')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->est_defaut)
                    ->action(function ($record) {
                        $record->est_defaut = false;
                        $record->save();
                        Notification::make()
                            ->success()
                            ->title('Adresse retirée des favoris')
                            ->send();
                    }),

                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),

                Action::make('copy_address')
                    ->label('Copier l\'adresse')
                    ->icon('heroicon-m-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newAddress = $record->replicate();
                        $newAddress->est_defaut = false;
                        $newAddress->save();

                        Notification::make()
                            ->success()
                            ->title('Adresse dupliquée')
                            ->body('Une copie de l\'adresse a été créée')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les adresses sélectionnées')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_set_default')
                        ->label('Définir par défaut')
                        ->icon('heroicon-m-star')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->definirCommeDefaut();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Aucune adresse')
            ->emptyStateDescription('Créez une adresse pour faciliter la livraison et la facturation.')
            ->emptyStateIcon('heroicon-o-map-pin')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
