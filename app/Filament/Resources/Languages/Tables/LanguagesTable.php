<?php

namespace App\Filament\Resources\Languages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('name_native')
                    ->label('Nom natif')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('dir')
                    ->label('Direction')
                    ->icon(fn ($state) => $state === 'rtl' ? 'heroicon-o-arrow-right' : 'heroicon-o-arrow-left')
                    ->color(fn ($state) => $state === 'rtl' ? 'warning' : 'success')
                    ->tooltip(fn ($state) => $state === 'rtl' ? 'Droite à gauche' : 'Gauche à droite'),
            ])
            ->filters([
                SelectFilter::make('dir')
                    ->label('Direction')
                    ->options([
                        'ltr' => 'LTR',
                        'rtl' => 'RTL',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
