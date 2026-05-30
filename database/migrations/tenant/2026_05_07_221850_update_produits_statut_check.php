<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Supprime l'ancienne contrainte (si elle existe)
        DB::statement('ALTER TABLE produits DROP CONSTRAINT IF EXISTS produits_statut_check');
        // Recrée avec les valeurs souhaitées
        DB::statement("ALTER TABLE produits ADD CONSTRAINT produits_statut_check CHECK (statut IN ('brouillon','publie','desactive','en_rupture','discontinued','archive','out_of_stock'))");
    }

    public function down()
    {
        DB::statement('ALTER TABLE produits DROP CONSTRAINT IF EXISTS produits_statut_check');
        DB::statement("ALTER TABLE produits ADD CONSTRAINT produits_statut_check CHECK (statut IN ('brouillon','publie','desactive','en_rupture','discontinued'))");
    }
};
