<?php

namespace App\Filament\Resources\Countries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('flag')
                    ->label('Flag')
                    ->square()
                    ->imageWidth(40)
                    ->imageHeight(20)
                    ->getStateUsing(fn ($record) => 'https://flagcdn.com/w40/'.strtolower($record->iso2).'.png'
                    ),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('iso2')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('iso3')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('phone_code')
                    ->prefix('+')
                    ->badge()
                    ->color('success'),

                TextColumn::make('region')
                    ->badge()
                    ->color('info'),

                TextColumn::make('subregion')
                    ->limit(20),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Actif' : 'Inactif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->defaultSort('name')
            ->striped()
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
