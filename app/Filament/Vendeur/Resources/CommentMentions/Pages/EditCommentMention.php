<?php

namespace App\Filament\Vendeur\Resources\CommentMentions\Pages;

use App\Filament\Vendeur\Resources\CommentMentions\CommentMentionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommentMention extends EditRecord
{
    protected static string $resource = CommentMentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
