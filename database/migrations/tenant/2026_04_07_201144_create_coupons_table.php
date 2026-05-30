<?php

// database/migrations/2024_01_01_000008_create_coupons_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('nom')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['pourcentage', 'montant_fixe', 'livraison_offerte']);
            $table->decimal('valeur', 12, 2);
            $table->decimal('minimum_panier', 12, 2)->nullable();
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->integer('utilisation_max')->nullable();
            $table->integer('utilisation_par_utilisateur')->nullable();
            $table->integer('total_utilise')->default(0);
            $table->boolean('est_actif')->default(true);
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->jsonb('produits_applicables')->nullable();
            $table->jsonb('categories_applicables')->nullable();
            $table->jsonb('produits_exclus')->nullable();
            $table->jsonb('utilisateurs_applicables')->nullable();
            $table->boolean('premiere_commande')->default(false);
            $table->boolean('cumulable')->default(false);
            $table->boolean('free_shipping')->default(false);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'est_actif']);
            $table->index(['date_debut', 'date_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
