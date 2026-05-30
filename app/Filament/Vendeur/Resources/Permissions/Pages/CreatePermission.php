<?php

namespace App\Filament\Vendeur\Resources\Permissions\Pages;

use App\Filament\Vendeur\Resources\Permissions\PermissionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;
}
