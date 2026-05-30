<?php

namespace App\Filament\Resources\Currencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CurrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('country.flag')
                    ->getStateUsing(fn ($record) => 'https://flagcdn.com/w40/'.strtolower($record->country->iso2).'.png'
                    )->square()
                    ->imageWidth(40)
                    ->imageHeight(20)
                    ->tooltip(fn ($record) => $record->country?->name),
                // Drapeau du pays associé (si relation country existe)
                // TextColumn::make('country.iso2')
                //     ->label('')
                //     ->formatStateUsing(fn ($state, $record) => $state
                //         ? '<img src="https://flagcdn.com/w40/'.Str::lower($state).'.png" alt="'.$record->country?->name.'" style="width:28px;height:auto;border:1px solid #e5e7eb;border-radius:2px;" />'
                //         : '—')
                //     ->html()
                //     ->alignCenter()
                //     ->tooltip(fn ($record) => $record->country?->name),

                TextColumn::make('country.name')
                    ->label('Pays')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Devise')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('symbol_first')
                    ->formatStateUsing(fn ($state) => $state ? 'Avant' : 'Après')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('symbol')
                    ->label('Symbole')
                    ->searchable(),

                TextColumn::make('symbol_native')
                    ->label('Symbole natif')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('precision')
                    ->label('Décimales')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('format_example')
                    ->label('Exemple')
                    ->state(function ($record): string {
                        $value = 1234.56;
                        $formatted = number_format(
                            $value,
                            $record->precision,
                            $record->decimal_mark,
                            $record->thousands_separator
                        );

                        return $record->symbol_first
                            ? $record->symbol.$formatted
                            : $formatted.$record->symbol;
                    })
                    ->toggleable(),
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
            ])
            ->defaultSort('country.name');
    }
}
