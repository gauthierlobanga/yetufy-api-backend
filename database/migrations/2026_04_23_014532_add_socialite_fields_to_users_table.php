<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter les champs pour l'authentification sociale si non existants
            if (! Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable()->after('provider');
            }

            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('provider_id');
            }

            // Ajouter un index unique sur (provider, provider_id) pour éviter les doublons
            if (! Schema::hasTable('users') || Schema::hasColumns('users', ['provider', 'provider_id'])) {
                // Index composite sur provider et provider_id
                $table->unique(['provider', 'provider_id'], 'unique_provider_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer les colonnes si elles existent
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }

            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropColumn('provider_id');
            }

            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }

            // Supprimer l'index unique
            if (Schema::hasTable('users')) {
                try {
                    $table->dropUnique('unique_provider_id');
                } catch (Throwable $e) {
                    // Index n'existe pas, continuer
                }
            }
        });
    }
};
