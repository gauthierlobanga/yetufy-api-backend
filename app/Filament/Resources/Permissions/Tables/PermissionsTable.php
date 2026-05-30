<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Permission copiée')
                    ->toggleable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'web' => 'primary',
                        'api' => 'warning',
                        'sanctum' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('roles_count')
                    ->label('Rôles')
                    ->counts('roles')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->toggleable(),

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
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->preload()
                    ->searchable()
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                        'sanctum' => 'Sanctum',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-m-pencil-square'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les permissions sélectionnées')
                        ->modalDescription('Cette action peut affecter les rôles qui utilisent ces permissions.')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                ]),
            ])
            ->emptyStateHeading('Aucune permission')
            ->emptyStateDescription('Les permissions sont généralement créées automatiquement par les packages ou via les seeders.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->poll('60s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('name', 'asc');
    }
}
