<?php

namespace App\Filament\Vendeur\Resources\LivraisonPaniers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LivraisonPaniersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('panier.client.nom')
                    ->label('Client')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('adresse.ville')
                    ->label('Ville')
                    ->searchable(),

                TextColumn::make('mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'domicile' => 'primary',
                        'point_relais' => 'info',
                        'express' => 'warning',
                        'retrait_magasin' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('cout')
                    ->label('Coût')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('date_estimee')
                    ->label('Date estimée')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('selected_at')
                    ->label('Sélectionné le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('mode')
                    ->label('Mode de livraison')
                    ->options([
                        'domicile' => 'Livraison à domicile',
                        'point_relais' => 'Point relais',
                        'express' => 'Livraison express',
                        'retrait_magasin' => 'Retrait en magasin',
                    ]),

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
            ->emptyStateHeading('Aucune livraison')
            ->emptyStateIcon('heroicon-o-truck')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession();
    }
}
