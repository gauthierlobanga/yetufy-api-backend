<?php

namespace App\Filament\Resources\CommentLikes\Pages;

use App\Filament\Resources\CommentLikes\CommentLikeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommentLike extends CreateRecord
{
    protected static string $resource = CommentLikeResource::class;
}
