<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE clients
                ADD COLUMN full_name TEXT GENERATED ALWAYS AS (
                    CASE
                        WHEN type = \'entreprise\' AND societe IS NOT NULL THEN societe
                        WHEN type = \'professionnel\' AND societe IS NOT NULL THEN societe || \' (\' || COALESCE(nom, \'\') || \' \' || COALESCE(prenom, \'\') || \')\'
                        ELSE COALESCE(civilite, \'\') || \' \' || COALESCE(prenom, \'\') || \' \' || COALESCE(nom, \'\')
                    END
                ) STORED
            ');
        } else {
            // Pour MySQL
            DB::statement('
                ALTER TABLE clients
                ADD COLUMN full_name TEXT GENERATED ALWAYS AS (
                    CONCAT_WS(\' \',
                        civilite,
                        prenom,
                        nom,
                        CASE
                            WHEN type = \'entreprise\' THEN societe
                            WHEN type = \'professionnel\' THEN CONCAT(societe, \' (\', nom, \' \', prenom, \')\')
                            ELSE NULL
                        END
                    )
                ) STORED
            ');
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }
};
