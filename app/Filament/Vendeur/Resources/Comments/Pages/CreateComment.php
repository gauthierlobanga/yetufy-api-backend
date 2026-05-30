<?php

namespace App\Filament\Vendeur\Resources\Comments\Pages;

use App\Filament\Vendeur\Resources\Comments\CommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;
}
