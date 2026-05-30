<?php

namespace App\Tenancy\Bootstrappers;

use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class CustomFilesystemTenancyBootstrapper extends FilesystemTenancyBootstrapper
{
    protected function getStoragePath(Tenant $tenant): string
    {
        // Utiliser l'UUID du tenant au lieu du slug pour éviter les conflits
        $prefix = config('tenancy.filesystem.suffix_base', 'tenant');

        return storage_path($prefix.$tenant->id);
    }
}
