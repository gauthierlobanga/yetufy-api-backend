<?php

namespace App\Filament\Vendeur\Resources\Posts\Pages;

use App\Filament\Vendeur\Resources\Posts\PostResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Post created')
            ->color('success')
            ->success()
            ->body('The post has been created successfully.')
            ->broadcast(Auth::user());
    }
}
