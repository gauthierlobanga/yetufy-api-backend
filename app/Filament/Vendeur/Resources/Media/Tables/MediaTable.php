<?php

namespace App\Filament\Vendeur\Resources\Media\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Image preview
                ImageColumn::make('preview')
                    ->label('Aperçu')
                    ->square()
                    ->imageHeight(56)
                    ->imageWidth(80)
                    ->getStateUsing(function ($record) {
                        if (str_starts_with($record->mime_type, 'image/')) {
                            // Les médias sont stockés sur le disque public central
                            // Utiliser l'URL standard
                            return $record->getUrl();
                        }

                        return null;
                    })
                    ->defaultImageUrl(function ($record) {
                        // Icône par type de fichier
                        if (str_starts_with($record->mime_type, 'video/')) {
                            return 'https://img.icons8.com/color/48/video-file.png';
                        }
                        if (str_starts_with($record->mime_type, 'application/pdf')) {
                            return 'https://img.icons8.com/color/48/pdf.png';
                        }
                        if (str_starts_with($record->mime_type, 'application/zip') ||
                            str_starts_with($record->mime_type, 'application/x-rar')) {
                            return 'https://img.icons8.com/color/48/zip.png';
                        }
                        if (str_starts_with($record->mime_type, 'text/')) {
                            return 'https://img.icons8.com/color/48/txt.png';
                        }

                        return 'https://img.icons8.com/color/48/file.png';
                    }),

                // Informations principales
                TextColumn::make('file_name')
                    ->label('Fichier')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name)
                    ->weight('medium')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->file_name)
                    ->copyable()
                    ->copyMessage('Nom du fichier copié'),

                // Taille formatée
                TextColumn::make('size')
                    ->label('Taille')
                    ->formatStateUsing(fn ($state): string => Number::fileSize($state ?? 0))
                    ->sortable()
                    ->icon('heroicon-m-document-arrow-down'),

                // Type MIME avec badge
                TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'image/') => 'success',
                        str_starts_with($state, 'video/') => 'warning',
                        str_starts_with($state, 'application/pdf') => 'danger',
                        str_starts_with($state, 'text/') => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (true) {
                        str_starts_with($state, 'image/') => 'Image',
                        str_starts_with($state, 'video/') => 'Vidéo',
                        str_starts_with($state, 'application/pdf') => 'PDF',
                        str_starts_with($state, 'text/') => 'Texte',
                        default => Str::upper(str_replace('application/', '', $state)),
                    }),

                // Collection avec badge coloré
                TextColumn::make('collection_name')
                    ->label('Collection')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'featured' => 'primary',
                        'gallery' => 'success',
                        'attachments' => 'warning',
                        'avatar' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Str::title(str_replace('_', ' ', $state)))
                    ->searchable()
                    ->sortable(),

                // Modèle associé
                TextColumn::make('model_type')
                    ->label('Modèle')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                // ID du modèle
                TextColumn::make('model_id')
                    ->label('ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // UUID
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyMessage('UUID copié'),

                // Disque
                TextColumn::make('disk')
                    ->label('Disque')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Ordre
                TextColumn::make('order_column')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Icône de conversion
                IconColumn::make('has_conversions')
                    ->label('Conversions')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn ($record) => $record->hasGeneratedConversion('thumb'))
                    ->toggleable(isToggledHiddenByDefault: true),

                // Dates
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('collection_name')
                    ->label('Collection')
                    ->options([
                        'featured' => 'À la une',
                        'gallery' => 'Galerie',
                        'attachments' => 'Pièces jointes',
                        'avatar' => 'Avatar',
                    ])
                    ->multiple()
                    ->searchable(),

                SelectFilter::make('disk')
                    ->label('Disque')
                    ->options([
                        'public' => 'Public',
                        'local' => 'Local',
                        's3' => 'S3',
                    ])
                    ->multiple(),

                SelectFilter::make('mime_type')
                    ->label('Type de fichier')
                    ->options([
                        'image' => 'Images',
                        'video' => 'Vidéos',
                        'application/pdf' => 'PDF',
                        'text' => 'Textes',
                        'archive' => 'Archives',
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->where(function ($q) use ($data) {
                            foreach ($data['value'] as $type) {
                                match ($type) {
                                    'image' => $q->orWhere('mime_type', 'like', 'image/%'),
                                    'video' => $q->orWhere('mime_type', 'like', 'video/%'),
                                    'application/pdf' => $q->orWhere('mime_type', 'application/pdf'),
                                    'text' => $q->orWhere('mime_type', 'like', 'text/%'),
                                    'archive' => $q->orWhere('mime_type', 'like', 'application/zip')
                                        ->orWhere('mime_type', 'like', 'application/x-rar'),
                                    default => null,
                                };
                            }
                        });
                    }),

                SelectFilter::make('model_type')
                    ->label('Modèle associé')
                    ->options(function () {
                        $models = Media::distinct()
                            ->pluck('model_type')
                            ->filter()
                            ->mapWithKeys(fn ($type) => [$type => class_basename($type)]);

                        return $models->toArray();
                    })
                    ->multiple()
                    ->searchable(),

                TernaryFilter::make('has_conversions')
                    ->label('Conversions générées')
                    ->placeholder('Tous')
                    ->trueLabel('Avec conversions')
                    ->falseLabel('Sans conversions')
                    ->queries(
                        true: fn ($query) => $query->whereRaw('JSON_LENGTH(generated_conversions) > 0'),
                        false: fn ($query) => $query->whereRaw('JSON_LENGTH(generated_conversions) = 0'),
                    ),

                TernaryFilter::make('created_at')
                    ->label('Date de création')
                    ->placeholder('Toutes')
                    ->trueLabel('Ce mois')
                    ->falseLabel('Ce trimestre')
                    ->queries(
                        true: fn ($query) => $query->whereMonth('created_at', now()->month),
                        false: fn ($query) => $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]),
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading('Modifier le média')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nom')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('order_column')
                                ->label('Ordre')
                                ->numeric()
                                ->default(0),
                            KeyValue::make('custom_properties')
                                ->label('Propriétés personnalisées')
                                ->columnSpanFull(),
                        ]),
                ])->badge()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Supprimer les médias sélectionnés')
                        ->modalDescription('Cette action est irréversible. Les fichiers seront supprimés du disque.')
                        ->modalSubmitActionLabel('Oui, supprimer'),
                ]),
            ])
            ->emptyStateHeading('Aucun média')
            ->emptyStateDescription('Commencez par importer des images ou des fichiers via les ressources associées.')
            ->emptyStateIcon('heroicon-o-photo')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->defaultSort('created_at', 'desc');
    }
}
