<?php

namespace App\Filament\Resources\CommentMentions\Pages;

use App\Filament\Resources\CommentMentions\CommentMentionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommentMentions extends ListRecords
{
    protected static string $resource = CommentMentionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
