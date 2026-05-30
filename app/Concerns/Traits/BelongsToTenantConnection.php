<?php

namespace App\Concerns\Traits;

trait BelongsToTenantConnection
{
    public function getConnectionName()
    {
        if (tenancy()->initialized) {
            return 'tenant';
        }

        return parent::getConnectionName();
    }
}
