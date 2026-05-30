<?php

namespace App\Filament\Vendeur\Resources\Entrepots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class EntrepotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable(),

                TextColumn::make('ville')
                    ->label('Ville')
                    ->getStateUsing(fn ($record) => $record->adresse ? preg_match('/\d{5}\s+([^,]+)/', $record->adresse, $matches) ? $matches[1] : '-' : '-')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('produits_count')
                    ->label('Produits')
                    ->counts('produits')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('stock_total')
                    ->label('Stock total')
                    ->getStateUsing(fn ($record) => $record->produits()->sum('produit_entrepot.quantite'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                TextColumn::make('valeur_stock')
                    ->label('Valeur stock')
                    ->getStateUsing(fn ($record) => $record->produits()->sum(DB::raw('produit_entrepot.quantite * produits.prix_ttc')))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('est_principal')
                    ->label('Principal')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('est_principal', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucun entrepôt')
            ->emptyStateDescription('Créez un entrepôt pour gérer vos stocks.')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('est_principal', 'desc');
    }
}
