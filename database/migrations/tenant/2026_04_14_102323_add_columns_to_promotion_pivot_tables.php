<?php

// database/migrations/2024_01_01_000000_add_columns_to_promotion_pivot_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table promotion_produit
        Schema::table('promotion_produit', function (Blueprint $table) {
            if (! Schema::hasColumn('promotion_produit', 'quantite_minimale')) {
                $table->integer('quantite_minimale')->default(1);
            }
            if (! Schema::hasColumn('promotion_produit', 'quantite_maximale')) {
                $table->integer('quantite_maximale')->nullable();
            }
            if (! Schema::hasColumn('promotion_produit', 'est_actif')) {
                $table->boolean('est_actif')->default(true);
            }
            if (! Schema::hasColumn('promotion_produit', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Table promotion_client
        Schema::table('promotion_client', function (Blueprint $table) {
            if (! Schema::hasColumn('promotion_client', 'utilisations_max')) {
                $table->integer('utilisations_max')->nullable();
            }
            if (! Schema::hasColumn('promotion_client', 'est_actif')) {
                $table->boolean('est_actif')->default(true);
            }
            if (! Schema::hasColumn('promotion_client', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (! Schema::hasColumn('promotion_client', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Table promotion_panier
        Schema::table('promotion_panier', function (Blueprint $table) {
            if (! Schema::hasColumn('promotion_panier', 'code_saisi')) {
                $table->string('code_saisi')->nullable();
            }
            if (! Schema::hasColumn('promotion_panier', 'est_manuelle')) {
                $table->boolean('est_manuelle')->default(false);
            }
            if (! Schema::hasColumn('promotion_panier', 'details')) {
                $table->json('details')->nullable();
            }
            if (! Schema::hasColumn('promotion_panier', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('promotion_produit', function (Blueprint $table) {
            $table->dropColumn(['quantite_minimale', 'quantite_maximale', 'est_actif', 'deleted_at']);
        });

        Schema::table('promotion_client', function (Blueprint $table) {
            $table->dropColumn(['utilisations_max', 'est_actif', 'notes', 'deleted_at']);
        });

        Schema::table('promotion_panier', function (Blueprint $table) {
            $table->dropColumn(['code_saisi', 'est_manuelle', 'details', 'deleted_at']);
        });
    }
};
