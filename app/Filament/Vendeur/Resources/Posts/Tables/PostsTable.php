<?php

namespace App\Filament\Vendeur\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->icon(Heroicon::DocumentText)
                    ->iconColor('primary')
                    ->label('Titre')
                    ->limit(30)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('md')
                    ->description(fn ($record): string => $record->getPlainTextContent(40))
                    ->tooltip(fn ($record): string => $record->title),

                SpatieMediaLibraryImageColumn::make('featured')
                    ->collection('featured')
                    ->label('Image')
                    ->conversion('thumb')
                    ->visibility('public')
                    ->imageSize(50)
                    ->square(),

                TextColumn::make('user.name')
                    ->label('Auteur')
                    ->icon('heroicon-m-user')
                    ->size('md')
                    ->description(fn ($record) => $record->user->email)
                    ->toggleable(),

                SpatieMediaLibraryImageColumn::make('gallery')
                    ->collection('gallery')
                    ->label('Galerie')
                    ->conversion('thumb')
                    ->visibility('public')
                    ->imageHeight(40)
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->size('md')
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'scheduled' => 'warning',
                        'expired' => 'danger',
                        'archived' => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'published' => 'heroicon-m-check-circle',
                        'draft' => 'heroicon-m-pencil',
                        'scheduled' => 'heroicon-m-clock',
                        'expired' => 'heroicon-m-x-circle',
                        'archived' => 'heroicon-m-archive-box',
                    })
                    ->sortable(),

                TextColumn::make('categories.nom')
                    ->label('Catégories')
                    ->badge()
                    ->size('md')
                    ->separator(',')
                    ->color('gray')
                    ->searchable(),

                SpatieTagsColumn::make('tags')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_pinned')
                    ->label('Épinglé')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('primary')
                    ->falseColor('gray'),

                TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('views_count')
                    ->label('Vues')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->size('md')
                    ->icon('heroicon-m-eye')
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
                TrashedFilter::make()
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'published' => 'Publié',
                        'scheduled' => 'Programmé',
                        'expired' => 'Expiré',
                        'archived' => 'Archivé',
                    ])
                    ->preload()
                    ->searchable()
                    ->multiple(),

                SelectFilter::make('user_id')
                    ->label('Auteur')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                SelectFilter::make('categories')
                    ->label('Catégories')
                    ->relationship('categories', 'nom')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                TernaryFilter::make('is_pinned')
                    ->label('Épinglé')
                    ->native(false)
                    ->trueLabel('Épinglé')
                    ->falseLabel('Non épinglé'),

                TernaryFilter::make('is_published')
                    ->label('Publié')
                    ->native(false)
                    ->trueLabel('Post publié')
                    ->falseLabel('Non publié')
                    ->queries(
                        true: fn ($query) => $query->published(),
                        false: fn ($query) => $query->where('status', '!=', 'published'),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->iconSize(IconSize::Medium),
                    EditAction::make()
                        ->iconSize(IconSize::Medium)
                        ->color('gray'),
                    DeleteAction::make()
                        ->iconSize(IconSize::Medium),
                    Action::make('preview')
                        ->label('Aperçu')
                        ->iconSize(IconSize::Medium)
                        ->icon('heroicon-m-eye')
                        ->url(fn ($record) => route('api.blog.show', $record->slug))
                        ->openUrlInNewTab(),
                ])
                    ->badge()
                    ->size(Size::Medium)
                    ->label(''),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('change_status')
                        ->label('Changer le statut')
                        ->icon('heroicon-m-arrow-path')
                        ->schema([
                            Select::make('status')
                                ->label('Nouveau statut')
                                ->options(Post::getStatuses())
                                ->preload()
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        }),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc')
            ->poll('60s');
    }
}
