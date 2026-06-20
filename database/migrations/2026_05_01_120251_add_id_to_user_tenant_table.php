<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : Ajouter la colonne 'id' UUID nullable si elle n'existe pas
        if (! Schema::hasColumn('user_tenant', 'id')) {
            Schema::table('user_tenant', function (Blueprint $table) {
                $table->uuid('id')->nullable()->first();
            });

            // Remplir les valeurs NULL avec des UUIDs générés
            DB::statement('UPDATE user_tenant SET id = gen_random_uuid() WHERE id IS NULL');
        }

        // Étape 2 : Supprimer l'ancienne clé primaire composite si elle existe
        // Le nom par défaut généré par Laravel est user_tenant_pkey
        try {
            DB::statement('ALTER TABLE user_tenant DROP CONSTRAINT IF EXISTS user_tenant_pkey');
        } catch (Exception $e) {
            // La contrainte n'existe peut-être pas déjà
        }

        // Étape 3 : Rendre la colonne 'id' NOT NULL (si ce n'est pas déjà fait)
        Schema::table('user_tenant', function (Blueprint $table) {
            $table->uuid('id')->nullable(false)->change();
        });

        // Étape 4 : Définir la nouvelle clé primaire sur 'id'
        Schema::table('user_tenant', function (Blueprint $table) {
            $table->primary('id');
        });

        // Étape 5 : Garantir l'unicité de la paire tenant_id / user_id
        Schema::table('user_tenant', function (Blueprint $table) {
            $table->unique(['tenant_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('user_tenant', function (Blueprint $table) {
            $table->dropPrimary('user_tenant_pkey');
            $table->dropUnique(['tenant_id', 'user_id']);
            $table->dropColumn('id');
            $table->primary(['tenant_id', 'user_id']);
        });
    }
};
