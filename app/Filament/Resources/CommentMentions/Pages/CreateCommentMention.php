<?php

namespace App\Filament\Resources\CommentMentions\Pages;

use App\Filament\Resources\CommentMentions\CommentMentionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommentMention extends CreateRecord
{
    protected static string $resource = CommentMentionResource::class;
}
