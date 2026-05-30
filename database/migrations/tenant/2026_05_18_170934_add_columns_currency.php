<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            // 1. Vérifier si la colonne existe déjà (au cas où)
            if (Schema::hasColumn('produits', 'devise_id')) {
                // Supprimer l'éventuelle contrainte étrangère existante
                $table->dropForeign(['devise_id']);
                // Supprimer la colonne
                $table->dropColumn('devise_id');
            }

            // 2. Ajouter la colonne devise_id comme nullable
            $table->uuid('devise_id')->nullable();

            // 3. Ajouter la clé étrangère (sans contrainte NOT NULL)
            $table->foreign('devise_id')
                ->references('id')
                ->on('devises')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropForeign(['devise_id']);
            $table->dropColumn('devise_id');
        });
    }
};
