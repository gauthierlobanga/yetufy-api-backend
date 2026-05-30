<?php

namespace App\Filament\Vendeur\Resources\Brands\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Logo
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->collection('logo')
                    ->imageSize(40)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=6B7280&color=fff')
                    ->toggleable(),

                // Nom et infos
                TextColumn::make('name')
                    ->label('Marque')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->slug)
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('website')
                    ->label('Site web')
                    ->searchable()
                    ->icon('heroicon-m-globe-alt')
                    ->url(fn ($record) => $record->website, true)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Couleur
                ColorColumn::make('color')
                    ->label('Couleur')
                    ->copyable()
                    ->toggleable(),

                // Statistiques
                TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('active_products_count')
                    ->label('Actifs')
                    ->counts('activeProducts')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Visibilité
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('À la une')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->sortable()
                    ->toggleable(),

                // Ordre
                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

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
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Statut actif')
                    ->placeholder('Toutes les marques')
                    ->trueLabel('Marques actives')
                    ->falseLabel('Marques inactives'),

                TernaryFilter::make('is_featured')
                    ->label('À la une')
                    ->placeholder('Toutes')
                    ->trueLabel('En vedette')
                    ->falseLabel('Non vedette'),

                TrashedFilter::make()
                    ->label('Corbeille'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('')
                    ->button()
                    ->icon('heroicon-m-pencil-square'),

                Action::make('view_products')
                    ->label('Voir produits')
                    ->icon('heroicon-m-shopping-bag')
                    ->url(fn ($record) => route('filament.vendeur.products.resources.produits.index', [
                        'tableFilters[brand_id][values][]' => $record->id,
                    ]))
                    ->button()
                    ->color('info'),

                Action::make('toggle_featured')
                    ->button()
                    ->label(fn ($record) => $record->is_featured ? 'Retirer de la une' : 'Mettre à la une')
                    ->icon(fn ($record) => $record->is_featured ? 'heroicon-m-star' : 'heroicon-m-star')
                    ->color(fn ($record) => $record->is_featured ? 'warning' : 'gray')
                    ->action(fn ($record) => $record->update(['is_featured' => ! $record->is_featured])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les marques sélectionnées')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Action::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->emptyStateHeading('Aucune marque')
            ->emptyStateDescription('Créez votre première marque pour organiser vos produits.')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('sort_order');
    }
}
