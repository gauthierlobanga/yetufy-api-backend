<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Models\Media;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery();
    }

    public function getTabs(): array
    {
        return [
            // Tous les médias
            'all' => Tab::make('All')
                ->badge(Media::where('disk', 'public')->count())
                ->badgeColor('gray')
                // ->icon('heroicon-m-photo')
                ->iconPosition(IconPosition::Before),

            // Médias récents (7 derniers jours)
            'recent' => Tab::make('Récents')
                ->badge(Media::where('disk', 'public')->where('created_at', '>=', now()->subDays(7))->count())
                ->badgeColor('primary')
                ->icon('heroicon-m-clock')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7))),

            // Images uniquement
            'images' => Tab::make('Image')
                ->badge(Media::where('disk', 'public')->where('mime_type', 'like', 'image/%')->count())
                ->badgeColor('success')
                ->icon('heroicon-m-photo')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'like', 'image/%')),

            // Collection: Avatar
            'collection_avatar' => Tab::make('Avatar')
                ->badge(Media::where('disk', 'public')->where('collection_name', 'avatar')->count())
                ->badgeColor('info')
                ->icon('heroicon-m-user-circle')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', 'avatar')),

            // Documents PDF
            'pdfs' => Tab::make('PDF')
                ->badge(Media::where('disk', 'public')->where('mime_type', 'application/pdf')->count())
                ->badgeColor('danger')
                ->icon('heroicon-m-document')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'application/pdf')),

            // Documents texte
            'documents' => Tab::make('Document')
                ->badge(Media::where('disk', 'public')->whereIn('mime_type', [
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain',
                    'text/csv',
                ])->count())
                ->badgeColor('info')
                ->icon('heroicon-m-document-text')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('mime_type', [
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain',
                    'text/csv',
                ])),

            // Séparateur visuel
            'separator' => Tab::make('')
                ->visible(false),

            // Collection: À la une
            'collection_featured' => Tab::make('À la une')
                ->badge(Media::where('disk', 'public')->where('collection_name', 'featured')->count())
                ->badgeColor('primary')
                ->icon('heroicon-m-star')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', 'featured')),

            // Collection: Galerie
            'collection_gallery' => Tab::make('Galerie')
                ->badge(Media::where('disk', 'public')->where('collection_name', 'gallery')->count())
                ->badgeColor('success')
                ->icon('heroicon-m-square-3-stack-3d')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', 'gallery')),

            // Collection: Pièces jointes
            'collection_attachments' => Tab::make('Pièces jointes')
                ->badge(Media::where('disk', 'public')->where('collection_name', 'attachments')->count())
                ->badgeColor('warning')
                ->icon('heroicon-m-paper-clip')
                ->iconPosition(IconPosition::Before)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('collection_name', 'attachments')),

        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'recent';
    }
}
