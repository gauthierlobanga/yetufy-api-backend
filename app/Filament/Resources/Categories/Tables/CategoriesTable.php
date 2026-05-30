<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg')
                    ->description(fn ($record) => $record->full_path),

                TextColumn::make('parent.nom')
                    ->label('Catégorie parente')
                    ->icon('heroicon-m-folder')
                    ->placeholder('Racine')
                    ->size('lg')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->numeric()
                    ->sortable()
                    ->size('lg')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                IconColumn::make('est_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                IconColumn::make('est_visible_dans_menu')
                    ->label('Menu')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->size('lg')
                    ->alignCenter()
                    ->badge()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                TernaryFilter::make('est_active')
                    ->label('Actif')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs'),

                TernaryFilter::make('est_visible_dans_menu')
                    ->label('Visible dans menu')
                    ->trueLabel('Visibles')
                    ->falseLabel('Masquées'),

                SelectFilter::make('parent_id')
                    ->label('Catégorie parente')
                    ->relationship('parent', 'nom')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->iconSize(IconSize::Medium)
                        ->color('gray'),

                    Action::make('view_posts')
                        ->label('Voir les posts')
                        ->icon('heroicon-m-document-text')
                        ->iconSize(IconSize::Medium)
                        ->url(fn ($record) => route('filament.admin.posts.resources.posts.index', ['tableFilters[categories][values][0]' => $record->id]))
                        ->color('info'),

                    Action::make('toggle_active')
                        ->iconSize(IconSize::Medium)
                        ->label(fn ($record) => $record->est_active ? 'Désactiver' : 'Activer')
                        ->icon(fn ($record) => $record->est_active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                        ->color(fn ($record) => $record->est_active ? 'danger' : 'success')
                        ->action(fn ($record) => $record->update(['est_active' => ! $record->est_active])),

                ])->badge()
                    ->size(Size::Medium)
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activer')
                        ->icon('heroicon-m-check-circle')
                        ->action(fn ($records) => $records->each->update(['est_active' => true])),

                    BulkAction::make('deactivate')
                        ->label('Désactiver')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['est_active' => false])),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->paginationMode(PaginationMode::Cursor)
            ->defaultSort('ordre')
            ->reorderable('ordre');
    }
}
