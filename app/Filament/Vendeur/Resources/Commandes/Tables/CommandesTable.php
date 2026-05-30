<?php

namespace App\Filament\Vendeur\Resources\Commandes\Tables;

use App\Models\Commande;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommandesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Numéro de commande
                TextColumn::make('numero_commande')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Numéro de commande copié')
                    ->toggleable(),

                TextColumn::make('client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->client?->email)
                    ->toggleable(),

                // Montants
                TextColumn::make('sous_total')
                    ->label('Sous-total')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('taxe')
                    ->label('TVA')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('frais_livraison')
                    ->label('Livraison')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->toggleable(),

                // Mode de paiement
                TextColumn::make('mode_paiement')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'carte' => 'Carte bancaire',
                        'paypal' => 'PayPal',
                        'virement' => 'Virement',
                        'cheque' => 'Chèque',
                        'especes' => 'Espèces',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'carte' => 'primary',
                        'paypal' => 'info',
                        'virement' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),

                // Statut
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_attente' => 'warning',
                        'en_cours' => 'info',
                        'termine' => 'success',
                        'annule' => 'danger',
                        'rejete' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en_attente' => 'En attente',
                        'en_cours' => 'En cours',
                        'termine' => 'Terminée',
                        'annule' => 'Annulée',
                        'rejete' => 'Rejetée',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                // Paiement effectué
                IconColumn::make('est_payee')
                    ->label('Payée')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => $record->statut !== 'en_attente')
                    ->toggleable(),

                // Statistiques
                TextColumn::make('lignes_count')
                    ->label('Articles')
                    ->counts('lignes')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('total_articles')
                    ->label('Qté')
                    ->getStateUsing(fn ($record) => $record->lignes->sum('quantite'))
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Dates
                TextColumn::make('date_commande')
                    ->label('Date commande')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('date_paiement')
                    ->label('Date paiement')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_expedition')
                    ->label("Date d'expédition")
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_livraison')
                    ->label('Date livraison')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Timestamps
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
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(Commande::getStatuts())
                    ->multiple(),

                SelectFilter::make('mode_paiement')
                    ->label('Mode de paiement')
                    ->options([
                        'carte' => 'Carte bancaire',
                        'paypal' => 'PayPal',
                        'virement' => 'Virement',
                        'cheque' => 'Chèque',
                        'especes' => 'Espèces',
                    ])
                    ->multiple(),

                Filter::make('total_range')
                    ->label('Montant total')
                    ->form([
                        TextInput::make('min_total')
                            ->label('Montant minimum')
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('max_total')
                            ->label('Montant maximum')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_total'],
                                fn (Builder $query, $total): Builder => $query->where('total', '>=', $total),
                            )
                            ->when(
                                $data['max_total'],
                                fn (Builder $query, $total): Builder => $query->where('total', '<=', $total),
                            );
                    }),

                Filter::make('date_commande_range')
                    ->label('Période de commande')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Du'),
                        DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_commande', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_commande', '<=', $date),
                            );
                    }),

                TernaryFilter::make('has_paiement')
                    ->label('Paiement effectué')
                    ->placeholder('Toutes les commandes')
                    ->trueLabel('Commandes payées')
                    ->falseLabel('Commandes non payées')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('date_paiement'),
                        false: fn (Builder $query) => $query->whereNull('date_paiement'),
                    ),

                TrashedFilter::make()
                    ->label('Corbeille'),
            ])
            ->recordActions([
                Action::make('view_details')
                    ->label('Détails')
                    ->icon('heroicon-m-eye')
                    // ->url(fn ($record) => route('filament.admin.resources.commandes.view', $record))
                    ->color('gray'),

                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),

                Action::make('generate_invoice')
                    ->label('Facture')
                    ->icon('heroicon-m-document-text')
                    ->color('success')
                    // ->url(fn ($record) => route('tenant.commandes.invoice', $record))
                    ->openUrlInNewTab(),

                Action::make('update_status')
                    ->label('Changer statut')
                    ->icon('heroicon-m-arrows-right-left')
                    ->color('warning')
                    ->schema([
                        Select::make('statut')
                            ->label('Nouveau statut')
                            ->options(Commande::getStatuts())
                            ->required(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->statut = $data['statut'];
                        if ($data['statut'] === 'termine') {
                            $record->date_livraison = now();
                        } elseif ($data['statut'] === 'en_cours') {
                            $record->date_paiement = now();
                        }
                        if (! empty($data['notes'])) {
                            $record->notes = ($record->notes ? $record->notes."\n" : '').$data['notes'];
                        }
                        $record->save();
                    }),

                Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => ! in_array($record->statut, ['termine', 'annule']))
                    ->action(fn ($record) => $record->annuler()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les commandes sélectionnées')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_update_status')
                        ->label('Changer statut')
                        ->icon('heroicon-m-arrows-right-left')
                        ->schema([
                            Select::make('statut')
                                ->label('Nouveau statut')
                                ->options(Commande::getStatuts())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->statut = $data['statut'];
                                if ($data['statut'] === 'termine') {
                                    $record->date_livraison = now();
                                } elseif ($data['statut'] === 'en_cours') {
                                    $record->date_paiement = now();
                                }
                                $record->save();
                            }
                        }),
                ]),
            ])
            ->emptyStateHeading('Aucune commande')
            ->emptyStateDescription('Les commandes apparaîtront ici une fois que les clients auront passé commande.')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100, 250])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
