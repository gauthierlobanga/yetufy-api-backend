<?php

namespace App\Filament\Resources\Timezones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimezonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('country.flag')
                    ->label('Drapeaux')
                    ->getStateUsing(fn ($record) => 'https://flagcdn.com/w40/'.strtolower($record->country->iso2).'.png')
                    ->square()
                    ->imageWidth(40)
                    ->imageHeight(20)
                    ->tooltip(fn ($record) => $record->country?->name),

                TextColumn::make('name')
                    ->label('Timezone')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->abbreviation),
            ])
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Pays')
                    ->relationship('country', 'name')
                    ->searchable()
                    ->preload(),
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
