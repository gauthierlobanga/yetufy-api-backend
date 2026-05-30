<?php

namespace App\Filament\Vendeur\Resources\ProductCategories\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Icône ou image miniature
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->collection('image')
                    ->disk('public')
                    ->visibility('public')
                    ->imageSize(32)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->nom).'&background='.ltrim($record->color ?? '#6B7280', '#').'&color=fff&size=32')
                    ->toggleable(),

                SpatieMediaLibraryImageColumn::make('icon')
                    ->label('Icône')
                    ->circular()
                    ->collection('icon')
                    ->disk('public')
                    ->visibility('public')
                    ->imageSize(32)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->nom).'&background='.ltrim($record->color ?? '#6B7280', '#').'&color=fff&size=32')
                    ->toggleable(),
                SpatieMediaLibraryImageColumn::make('banner')
                    ->label('Banner')
                    ->circular()
                    ->collection('banner')
                    ->disk('public')
                    ->visibility('public')
                    ->imageSize(32)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->nom).'&background='.ltrim($record->color ?? '#6B7280', '#').'&color=fff&size=32')
                    ->toggleable(),

                // Nom avec badge de couleur et hiérarchie visuelle
                TextColumn::make('nom')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => str_repeat('— ', $record->level).$state)
                    ->weight('medium')
                    ->description(fn ($record) => $record->slug)
                    ->color(fn ($record) => $record->color),

                // Description courte
                TextColumn::make('short_description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                // Couleur
                ColorColumn::make('color')
                    ->label('Couleur')
                    ->copyable()
                    ->toggleable(),

                // Ordre
                TextColumn::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                // Nombre de produits
                TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                // Nombre de sous-catégories
                TextColumn::make('children_count')
                    ->label('Sous-catégories')
                    ->counts('children')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Visibilité
                IconColumn::make('est_active')
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
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('show_in_menu')
                    ->label('Menu')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
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
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->defaultSort('order')
            ->reorderable('order')
            ->filters([
                TernaryFilter::make('est_active')
                    ->label('Statut actif')
                    ->placeholder('Toutes les catégories')
                    ->trueLabel('Catégories actives')
                    ->searchable()
                    ->falseLabel('Catégories inactives'),

                TernaryFilter::make('is_featured')
                    ->label('À la une')
                    ->placeholder('Toutes')
                    ->trueLabel('En vedette')
                    ->falseLabel('Non vedette')
                    ->searchable(),

                TernaryFilter::make('show_in_menu')
                    ->label('Visible dans le menu')
                    ->placeholder('Toutes')
                    ->trueLabel('Visibles')
                    ->falseLabel('Non visibles')
                    ->searchable(),

                SelectFilter::make('parente_id')
                    ->label('Catégorie parente')
                    ->relationship('parent', 'nom')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make()
                    ->label('Corbeille')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view_products')
                        ->label('Voir produits')
                        ->icon('heroicon-m-shopping-bag')
                        ->url(fn ($record) => route('filament.vendeur.products.resources.produits.index', [
                            'tableFilters[categories][values][]' => $record->id,
                        ]))
                        ->color('info'),

                    Action::make('view_children')
                        ->label('Voir sous-catégories')
                        ->icon('heroicon-m-folder')
                        ->url(fn ($record) => route('filament.vendeur.products.resources.product-categories.index', [
                            'tableFilters[parent_id][value]' => $record->id,
                        ]))
                        ->color('gray')
                        ->visible(fn ($record) => $record->hasChildren()),

                    EditAction::make()
                        ->button()
                        ->label('')
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('duplicate')
                        ->label('Dupliquer')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('gray')
                        ->action(function ($record) {
                            $newCategory = $record->replicate();
                            $newCategory->nom = $record->nom.' (Copie)';
                            $newCategory->slug = Str::slug($newCategory->nom);
                            $newCategory->save();

                            // Copier les médias
                            foreach ($record->getMedia('image') as $media) {
                                $newCategory->addMedia($media->getPath())
                                    ->preservingOriginal()
                                    ->toMediaCollection('image');
                            }

                            foreach ($record->getMedia('icon') as $media) {
                                $newCategory->addMedia($media->getPath())
                                    ->preservingOriginal()
                                    ->toMediaCollection('icon');
                            }
                        }),
                ])
                    ->badge()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les catégories sélectionnées')
                        ->modalDescription('Les sous-catégories seront réassignées à la catégorie parente.')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['est_active ' => true])),
                    Action::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['est_active ' => false])),
                ]),
            ])
            ->emptyStateHeading('Aucune catégorie')
            ->emptyStateDescription('Créez votre première catégorie pour organiser vos produits.')
            ->emptyStateIcon('heroicon-o-folder')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100, 'all'])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('order');
    }
}
