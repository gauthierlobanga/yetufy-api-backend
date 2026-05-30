<?php

namespace App\Filament\Vendeur\Resources\CommentLikes\Pages;

use App\Filament\Vendeur\Resources\CommentLikes\CommentLikeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommentLikes extends ListRecords
{
    protected static string $resource = CommentLikeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
