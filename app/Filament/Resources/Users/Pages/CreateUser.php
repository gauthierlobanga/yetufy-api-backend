<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return Notification::make()
            ->title('User create successfully')
            ->getBroadcastMessage();
    }
}
