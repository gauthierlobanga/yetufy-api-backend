<?php

namespace App\Filament\Vendeur\Resources\AvisClients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AvisClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('client.nom') // Changé de 'name' à 'nom'
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->client?->email)
                    ->icon('heroicon-o-user'),

                TextColumn::make('produit.nom') // Assurez-vous que c'est 'nom' et non 'name'
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-shopping-bag'),

                TextColumn::make('note')
                    ->label('Note')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state)." ($state/5)")
                    ->color(fn ($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->icon(fn ($state) => match (true) {
                        $state >= 4 => 'heroicon-o-star',
                        $state >= 3 => 'heroicon-o-star',
                        default => 'heroicon-o-face-frown',
                    }),

                TextColumn::make('commentaire')
                    ->label('Commentaire')
                    ->limit(50)
                    ->searchable()
                    ->tooltip(fn ($record) => $record->commentaire)
                    ->toggleable(),

                IconColumn::make('approuve')
                    ->label('Statut')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => $record->approuve ? 'Approuvé' : 'En attente'),

                TextColumn::make('date_avis')
                    ->label('Date de l\'avis')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Mis à jour le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('approuve')
                    ->label('Statut d\'approbation')
                    ->options([
                        true => 'Approuvés',
                        false => 'En attente',
                    ]),

                SelectFilter::make('note')
                    ->label('Note minimum')
                    ->options([
                        1 => '⭐ ou plus',
                        2 => '⭐⭐ ou plus',
                        3 => '⭐⭐⭐ ou plus',
                        4 => '⭐⭐⭐⭐ ou plus',
                        5 => '⭐⭐⭐⭐⭐',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value) => $query->where('note', '>=', $value)
                        );
                    }),

                Filter::make('avec_reponse')
                    ->label('Avec réponse')
                    ->query(fn (Builder $query) => $query->whereNotNull('reponse')->where('reponse', '!=', '')),

                Filter::make('sans_reponse')
                    ->label('Sans réponse')
                    ->query(fn (Builder $query) => $query->whereNull('reponse')->orWhere('reponse', '')),

                TrashedFilter::make(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-o-pencil'),

                Action::make('approuver')
                    ->label('Approuver')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['approuve' => true]))
                    ->visible(fn ($record) => ! $record->approuve)
                    ->requiresConfirmation(),

                Action::make('desapprouver')
                    ->label('Désapprouver')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn ($record) => $record->update(['approuve' => false]))
                    ->visible(fn ($record) => $record->approuve)
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection'),

                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),

                    Action::make('approuver_selection')
                        ->label('Approuver la sélection')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['approuve' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('date_avis', 'desc')
            ->striped()
            ->poll('30s');
    }
}
