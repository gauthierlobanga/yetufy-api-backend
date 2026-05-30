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
        $supportsFullText = Schema::getConnection()->getDriverName() !== 'sqlite';

        Schema::create('produit_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->string('color')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->boolean('est_active')->default(true);
            $table->boolean('is_featured')->default(true)->after('est_active');
            $table->boolean('show_in_menu')->default(true)->after('is_featured');
            $table->string('short_description')->after('description');
            $table->string('seo_title')->after('short_description');
            $table->string('seo_description')->after('seo_title');
            $table->jsonb('seo_keywords')->after('seo_description');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['est_active', 'order']);
        });

        Schema::create('brands', function (Blueprint $table) use ($supportsFullText) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->jsonb('seo')->nullable();
            $table->jsonb('social_links')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
            if ($supportsFullText) {
                $table->fullText(['name', 'description']);
            }
        });

        Schema::create('produits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigIncrements('devise_id');
            $table->foreign('devise_id')->references('id')->on('currencies')->onDelete('restrict');
            $table->foreignUuid('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('reference')->nullable();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('description_longue')->nullable();
            $table->decimal('prix_ht', 10, 2);
            $table->decimal('prix_ttc', 10, 2);
            $table->decimal('prix_promotion', 10, 2)->nullable();
            $table->integer('quantite_stock')->default(0);
            $table->integer('seuil_alerte')->nullable();
            $table->string('sku')->nullable()->unique();
            $table->string('ean')->nullable();
            $table->decimal('poids', 8, 2)->nullable(); // en kg
            $table->decimal('hauteur', 8, 2)->nullable(); // en cm
            $table->decimal('largeur', 8, 2)->nullable();
            $table->decimal('profondeur', 8, 2)->nullable();
            $table->string('unite_mesure')->nullable();
            $table->jsonb('attributs')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->jsonb('attributes')->nullable();
            $table->enum('statut', ['brouillon', 'publie', 'desactive', 'en_rupture', 'discontinued'])->default('brouillon');
            $table->integer('vues')->default(0);
            $table->boolean('is_featured')->default(true)->after('statut');
            $table->boolean('is_new')->default(true)->after('is_featured');
            $table->boolean('is_bestseller')->default(true)->after('is_new');
            $table->integer('views_count')->default(0)->after('is_bestseller');
            $table->integer('sold_count')->default(0)->after('views_count');
            $table->decimal('average_rating', 10, 2)->default(0)->after('sold_count');
            $table->integer('reviews_count')->default(0)->after('average_rating');
            $table->string('seo_title')->after('short_description');
            $table->string('seo_description')->after('seo_title');
            $table->jsonb('seo_keywords')->after('seo_description');
            $table->timestamp('published_at')->nullable()->after('seo_keywords');
            $table->timestamp('scheduled_for')->nullable()->after('published_at');
            $table->timestamp('expires_at')->nullable()->after('scheduled_for');
            $table->timestamp('date_publication')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variante_produits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('order')->default(0);
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->string('nom');
            $table->string('valeur');
            $table->decimal('supplement_prix', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->string('sku_variante')->nullable()->unique();
            $table->jsonb('attributs')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->string('transaction_id')->nullable()->unique();
            $table->enum('mode', ['carte', 'paypal', 'virement', 'cheque', 'especes', 'crypto', 'mobile_money']);
            $table->string('carte_brand')->nullable();
            $table->string('carte_last4')->nullable();
            $table->decimal('montant', 12, 2);
            $table->string('devise', 3)->default('EUR');
            $table->enum('statut', ['en_attente', 'valide', 'echec', 'rembourse', 'partiel'])->default('en_attente');
            $table->jsonb('details')->nullable();
            $table->timestamp('date_paiement')->nullable();
            $table->timestamp('date_remboursement')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('statut');
            $table->index('transaction_id');
        });

        Schema::create('produit_categorie_pivot', function (Blueprint $table) {
            $table->foreignUuid('produit_id')->references('id')->on('produits')->onDelete('cascade');
            $table->foreignUuid('category_id')->references('id')->on('produit_categories')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->integer('order')->default(0);
            $table->index(['is_primary']);
            $table->primary(['produit_id', 'category_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produit_categories');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('produits');
        Schema::dropIfExists('variante_produits');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('produit_categorie_pivot');
    }
};
