<?php

namespace App\Filament\Vendeur\Resources\Produits\Tables;

use App\Models\Produit;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class ProduitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Image principale
                SpatieMediaLibraryImageColumn::make('image_principale')
                    ->label('Image')
                    ->square()
                    ->collection('image_principale')
                    ->disk('public')
                    ->visibility('public')
                    ->imageHeight(40)
                    ->imageWidth(50)
                    ->defaultImageUrl(fn ($record) => 'https://placehold.co/400x400?text='.urlencode($record->nom))
                    ->toggleable(),

                SpatieMediaLibraryImageColumn::make('images')
                    ->label('Images Produit')
                    ->circular()
                    ->collection('images')
                    ->limit(3)
                    ->stacked()
                    ->disk('public')
                    ->visibility('public')
                    ->imageSize(40)
                    ->defaultImageUrl(fn ($record) => 'https://placehold.co/400x400?text='.urlencode($record->nom))
                    ->toggleable(),

                // Informations produit
                TextColumn::make('nom')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->reference.' | SKU: '.$record->sku)
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->nom),

                // Référence
                TextColumn::make('reference')
                    ->label('Réf.')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // SKU
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                // Marque
                TextColumn::make('brand.name')
                    ->label('Marque')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                // Catégories
                TextColumn::make('categories.nom')
                    ->label('Catégories')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->searchable(),

                // Prix
                TextColumn::make('prix_ttc')
                    ->label('Prix TTC')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('success'),

                TextColumn::make('prix_promotion')
                    ->label('Promo')
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Stock
                TextColumn::make('quantite_stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success'))
                    ->formatStateUsing(fn ($state) => Number::format($state)),

                // Statut
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'publie' => 'success',
                        'brouillon' => 'warning',
                        'archive' => 'gray',
                        'out_of_stock' => 'danger',
                        'discontinued' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'publie' => 'heroicon-m-check-circle',
                        'brouillon' => 'heroicon-m-clock',
                        'archive' => 'heroicon-m-clock',
                        'out_of_stock' => 'heroicon-m-circle',
                        'discontinued' => 'heroicon-m-x-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'publie' => 'Publié',
                        'brouillon' => 'Brouillon',
                        'archive' => 'Archivé',
                        'out_of_stock' => 'Rupture',
                        'discontinued' => 'Abandonné',
                        default => $state,
                    })
                    ->sortable(),

                // Indicateurs
                IconColumn::make('is_featured')
                    ->label('Une')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->sortable()
                    ->tooltip('À la une')
                    ->toggleable(),

                IconColumn::make('is_new')
                    ->label('New')
                    ->boolean()
                    ->trueIcon('heroicon-o-sparkles')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('primary')
                    ->tooltip('Nouveauté')
                    ->toggleable(),

                IconColumn::make('is_bestseller')
                    ->label('Best')
                    ->boolean()
                    ->trueIcon('heroicon-o-fire')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('orange')
                    ->tooltip('Meilleure vente')
                    ->toggleable(),

                IconColumn::make('is_deal_of_the_day')
                    ->label('Deal du jour')
                    ->boolean()
                    ->trueIcon(Heroicon::Megaphone)
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('orange')
                    ->tooltip('Offres exceptionnels')
                    ->toggleable(),

                // Statistiques
                TextColumn::make('sold_count')
                    ->label('Vendus')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('views_count')
                    ->label('Vues')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('average_rating')
                    ->label('Note')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color('warning'),

                // Dimensions
                TextColumn::make('poids')
                    ->label('Poids')
                    ->suffix(' kg')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Dates
                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),

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
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->searchable()
                    ->preload()
                    ->options(Produit::getStatuses())
                    ->multiple(),

                SelectFilter::make('brand_id')
                    ->label('Marque')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('categories')
                    ->label('Catégories')
                    ->relationship('categories', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Filter::make('price_range')
                    ->label('Prix')
                    ->schema([
                        TextInput::make('min_price')
                            ->label('Prix min')
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('max_price')
                            ->label('Prix max')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('prix_ttc', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('prix_ttc', '<=', $price),
                            );
                    }),

                Filter::make('stock')
                    ->label('Stock')
                    ->schema([
                        Select::make('stock_status')
                            ->label('État du stock')
                            ->searchable()
                            ->preload()
                            ->options([
                                'in_stock' => 'En stock',
                                'low_stock' => 'Stock faible',
                                'out_of_stock' => 'Rupture',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['stock_status'] === 'in_stock',
                            fn (Builder $query) => $query->where('quantite_stock', '>', 0)
                        )->when(
                            $data['stock_status'] === 'low_stock',
                            fn (Builder $query) => $query->where('quantite_stock', '>', 0)->where('quantite_stock', '<=', 10)
                        )->when(
                            $data['stock_status'] === 'out_of_stock',
                            fn (Builder $query) => $query->where('quantite_stock', '<=', 0)
                        );
                    }),

                TernaryFilter::make('is_featured')
                    ->searchable()
                    ->preload()
                    ->label('À la une'),

                TernaryFilter::make('is_new')
                    ->searchable()
                    ->preload()
                    ->label('Nouveauté'),

                TernaryFilter::make('is_bestseller')
                    ->searchable()
                    ->preload()
                    ->label('Meilleure vente'),
                TernaryFilter::make('is_deal_of_the_day')
                    ->searchable()
                    ->preload()
                    ->label('Offres exceptionnels'),

                Filter::make('created_at')
                    ->label('Date de création')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Créé depuis'),
                        DatePicker::make('created_until')
                            ->label('Créé jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                TrashedFilter::make()
                    ->searchable()
                    ->preload()
                    ->label('Corbeille'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('preview')
                        ->label('Aperçu')
                        ->icon('heroicon-m-eye')
                        ->url(fn ($record) => route('tenant.product.show', $record->slug))
                        ->openUrlInNewTab()
                        ->color('gray'),

                    EditAction::make()
                        ->icon('heroicon-m-pencil-square'),

                    Action::make('duplicate')
                        ->label('Dupliquer')
                        ->icon('heroicon-m-document-duplicate')
                        ->color('gray')
                        ->action(function ($record) {
                            $newProduct = $record->replicate();
                            $newProduct->nom = $record->nom.' (Copie)';
                            $newProduct->slug = Str::slug($newProduct->nom);
                            $newProduct->statut = 'draft';
                            $newProduct->published_at = null;
                            $newProduct->save();

                            // Copier les images
                            foreach ($record->getMedia('images') as $media) {
                                $newProduct->addMedia($media->getPath())
                                    ->preservingOriginal()
                                    ->toMediaCollection('images');
                            }
                        }),

                    Action::make('toggle_featured')
                        ->label(fn ($record) => $record->is_featured ? 'Retirer de la une' : 'Mettre à la une')
                        ->icon(fn ($record) => $record->is_featured ? 'heroicon-m-star' : 'heroicon-m-star')
                        ->color(fn ($record) => $record->is_featured ? 'warning' : 'gray')
                        ->action(fn ($record) => $record->update(['is_featured' => ! $record->is_featured])),
                ])
                    ->badge()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les produits sélectionnés')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    Action::make('toggle_featured_bulk')
                        ->label('Mettre à la une')
                        ->icon('heroicon-m-star')
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    Action::make('update_status')
                        ->label('Changer le statut')
                        ->icon('heroicon-m-adjustments-horizontal')
                        ->schema([
                            Select::make('statut')
                                ->label('Nouveau statut')
                                ->options(Produit::getStatuses())
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['statut' => $data['statut']]);
                        }),
                ]),
            ])
            ->emptyStateHeading('Aucun produit')
            ->emptyStateDescription('Créez votre premier produit pour commencer à vendre.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100, 250])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
