<?php

namespace App\Tenancy\Bootstrappers;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class PostgreSQLSchemaBootstrapper implements TenancyBootstrapper
{
    public function __construct()
    {
        //
    }

    public function bootstrap(Tenant $tenant)
    {
        // Convert tenant ID to schema name (replace hyphens with underscores)
        $schemaName = str_replace('-', '_', $tenant->getTenantKey());
        
        // Set the search_path to the tenant's schema
        DB::statement("SET search_path TO \"{$schemaName}\", public");
    }

    public function revert()
    {
        // Reset search_path to public when tenancy ends
        DB::statement("SET search_path TO public");
    }
}
