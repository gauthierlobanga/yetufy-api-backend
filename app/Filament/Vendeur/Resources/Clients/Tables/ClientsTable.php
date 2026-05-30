<?php

namespace App\Filament\Vendeur\Resources\Clients\Tables;

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
use Illuminate\Support\Number;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('full_name')
                    ->label('Client')
                    ->searchable(query: function ($query, $search) {
                        return $query->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('societe', 'like', "%{$search}%");
                    })
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email)
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'particulier' => 'primary',
                        'professionnel' => 'warning',
                        'entreprise' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'particulier' => 'Particulier',
                        'professionnel' => 'Professionnel',
                        'entreprise' => 'Entreprise',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('societe')
                    ->label('Société')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_achats')
                    ->label('Total achats')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('nombre_commandes')
                    ->label('Commandes')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('panier_moyen')
                    ->label('Panier moyen')
                    ->getStateUsing(fn ($record) => Number::currency(
                        $record->nombre_commandes > 0
                            ? ($record->total_achats / $record->nombre_commandes)
                            : 0,
                        'EUR'
                    ))
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('a_des_commandes')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-shopping-cart')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => $record->nombre_commandes > 0)
                    ->toggleable(),

                TextColumn::make('date_premier_achat')
                    ->label('Premier achat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_dernier_achat')
                    ->label('Dernier achat')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->since()
                    ->toggleable(),

                TextColumn::make('jours_since_last_order')
                    ->label('Inactif depuis')
                    ->getStateUsing(fn ($record) => $record->date_dernier_achat
                        ? now()->diffInDays($record->date_dernier_achat).' jours'
                        : 'Jamais')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Type de client')
                    ->options([
                        'particulier' => 'Particulier',
                        'professionnel' => 'Professionnel',
                        'entreprise' => 'Entreprise',
                    ])
                    ->multiple(),

                TernaryFilter::make('a_des_commandes')
                    ->label('A déjà commandé')
                    ->placeholder('Tous')
                    ->trueLabel('Clients actifs')
                    ->falseLabel('Sans commande')
                    ->queries(
                        true: fn ($query) => $query->where('nombre_commandes', '>', 0),
                        false: fn ($query) => $query->where('nombre_commandes', 0),
                    ),

                SelectFilter::make('date_dernier_achat')
                    ->label('Dernière activité')
                    ->options([
                        '7' => '7 derniers jours',
                        '30' => '30 derniers jours',
                        '90' => '90 derniers jours',
                        '365' => '1 an',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            return $query->where('date_dernier_achat', '>=', now()->subDays((int) $data['value']));
                        }

                        return $query;
                    }),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_orders')
                    ->label('Voir commandes')
                    ->icon('heroicon-m-shopping-cart')
                    ->url(fn ($record) => route('filament.vendeur.resources.comments.index', [
                        'tableFilters[client_id][value]' => $record->id,
                    ]))
                    ->color('info'),

                Action::make('view_addresses')
                    ->label('Voir adresses')
                    ->icon('heroicon-m-map-pin')
                    ->url(fn ($record) => route('filament.vendeur.resources.adresses.index', [
                        'tableFilters[adressable_type][value]' => 'App\Models\Client',
                        'tableFilters[adressable_id][value]' => $record->id,
                    ]))
                    ->color('gray'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les clients sélectionnés')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun client')
            ->emptyStateDescription('Créez votre premier client pour commencer à vendre.')
            ->emptyStateIcon('heroicon-o-users')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100, 250])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
