<?php

namespace App\Filament\Clients\Resources\Users\Pages;

use App\Filament\Clients\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
