<?php

namespace App\Tenancy\TenantDatabaseManagers;

use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLSchemaManager;

class PostgreSQLSchemaManagerWithSearchPath extends PostgreSQLSchemaManager
{
    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        // Récupérer le nom de la base (déjà suffixé/préfixé par le bootstrapper)
        $name = $tenant->database()->getName();

        // Remplacer les tirets par des underscores (PostgreSQL n'aime pas les tirets dans les noms de schéma)
        $schemaName = str_replace('-', '_', $name);

        return $this->database()->statement("CREATE SCHEMA IF NOT EXISTS \"{$schemaName}\"");
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        $name = $tenant->database()->getName();
        $schemaName = str_replace('-', '_', $name);

        return $this->database()->statement("DROP SCHEMA IF EXISTS \"{$schemaName}\" CASCADE");
    }

    public function databaseExists(string $name): bool
    {
        $schemaName = $this->makeSchemaName($name);

        $result = DB::select('SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?', [$schemaName]);

        return count($result) > 0;
    }

    protected function makeSchemaName(string $name): string
    {
        // Convert hyphens to underscores for PostgreSQL schema names
        return str_replace('-', '_', $name);
    }
}
