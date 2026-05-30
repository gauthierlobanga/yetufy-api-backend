<?php

namespace App\Filament\Vendeur\Resources\Devises\Tables;

use App\Models\Devise;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DevisesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable(),

                TextColumn::make('symbole')
                    ->label('Symbole')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('taux_change')
                    ->label('Taux')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),

                IconColumn::make('est_reference')
                    ->label('Référence')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('set_as_reference')
                    ->label('Définir comme référence')
                    ->icon('heroicon-m-star')
                    ->color('warning')
                    ->visible(fn ($record) => ! $record->est_reference)
                    ->action(function ($record) {
                        Devise::query()->update(['est_reference' => false]);
                        $record->update(['est_reference' => true]);
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune devise')
            ->emptyStateDescription('Créez une devise pour gérer les prix en différentes devises.')
            ->emptyStateIcon('heroicon-o-currency-euro')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->defaultSort('code');
    }
}
