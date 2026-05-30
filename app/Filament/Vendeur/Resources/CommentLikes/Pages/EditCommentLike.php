<?php

namespace App\Filament\Vendeur\Resources\CommentLikes\Pages;

use App\Filament\Vendeur\Resources\CommentLikes\CommentLikeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommentLike extends EditRecord
{
    protected static string $resource = CommentLikeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
