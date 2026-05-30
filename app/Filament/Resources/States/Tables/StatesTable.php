<?php

namespace App\Filament\Resources\States\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('country.flag')
                    ->getStateUsing(fn ($record) => 'https://flagcdn.com/w40/'.strtolower($record->country->iso2).'.png'
                    )
                    ->square()
                    ->imageWidth(40)
                    ->imageHeight(20),

                TextColumn::make('name')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('country.name')
                    ->badge(),
            ])
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
