<?php

namespace App\Filament\Vendeur\Resources\Wishlists\Tables;

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

class WishlistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('client.nom')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->client?->email)
                    ->toggleable(),

                TextColumn::make('nom')
                    ->label('Nom de la liste')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->default('Ma liste de souhaits')
                    ->toggleable(),

                TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('products_count')
                    ->label('Produits distincts')
                    ->counts('items')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_publique')
                    ->label('Visibilité')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('share_url')
                    ->label('Lien')
                    ->formatStateUsing(fn ($record) => $record->est_publique ? 'Partageable' : 'Privé')
                    ->badge()
                    ->color(fn ($record) => $record->est_publique ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'nom')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('est_publique')
                    ->label('Visibilité')
                    ->placeholder('Toutes')
                    ->trueLabel('Publiques')
                    ->falseLabel('Privées'),

                TernaryFilter::make('has_items')
                    ->label('Avec articles')
                    ->placeholder('Toutes')
                    ->trueLabel('Avec articles')
                    ->falseLabel('Vides')
                    ->queries(
                        true: fn ($query) => $query->has('items'),
                        false: fn ($query) => $query->doesntHave('items'),
                    ),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('share')
                    ->label('Partager')
                    ->icon('heroicon-m-share')
                    ->color('success')
                    ->visible(fn ($record) => $record->est_publique)
                    ->action(function ($record) {
                        $url = route('tenant.wishlist.toggle', $record->id);
                        Notification::make()
                            ->success()
                            ->title('Lien de partage')
                            ->body($url)
                            ->duration(10000)
                            ->send();
                    }),

                Action::make('make_public')
                    ->label('Rendre publique')
                    ->icon('heroicon-m-globe-alt')
                    ->color('warning')
                    ->visible(fn ($record) => ! $record->est_publique)
                    ->action(fn ($record) => $record->update(['est_publique' => true])),

                Action::make('make_private')
                    ->label('Rendre privée')
                    ->icon('heroicon-m-lock-closed')
                    ->color('gray')
                    ->visible(fn ($record) => $record->est_publique)
                    ->action(fn ($record) => $record->update(['est_publique' => false])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('bulk_make_public')
                        ->label('Rendre publiques')
                        ->icon('heroicon-m-globe-alt')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['est_publique' => true])),
                    Action::make('bulk_make_private')
                        ->label('Rendre privées')
                        ->icon('heroicon-m-lock-closed')
                        ->color('gray')
                        ->action(fn ($records) => $records->each->update(['est_publique' => false])),
                ]),
            ])
            ->emptyStateHeading('Aucune liste de souhaits')
            ->emptyStateDescription('Les clients peuvent créer des listes de souhaits pour sauvegarder leurs produits préférés.')
            ->emptyStateIcon('heroicon-o-heart')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('created_at', 'desc');
    }
}
